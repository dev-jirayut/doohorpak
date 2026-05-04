<?php

namespace App\Http\Controllers;

use App\Models\AppErrorLog;
use App\Models\Contract;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $property = $request->get('current_property');

        $contracts = Contract::with(['rental.tenant', 'rental.room'])
            ->when($property, fn ($q) => $q->where('property_id', $property->id))
            ->latest()
            ->paginate(20);

        return view('contracts.index', compact('contracts'));
    }

    public function create(Request $request)
    {
        $startedAt = microtime(true);
        $property = $request->get('current_property');
        if (!$property) {
            return redirect()->route('dashboard')->with('error', 'กรุณาเลือกหอพักก่อนสร้างสัญญา');
        }

        $rentals  = Rental::query()
            ->select(['id', 'property_id', 'room_id', 'tenant_id', 'monthly_rent', 'start_date', 'status'])
            ->with([
                'tenant:id,name,phone',
                'room:id,property_id,room_number',
            ])
            ->where('property_id', $property->id)
            ->where('status', 'active')
            ->whereDoesntHave('contract', fn ($query) => $query->where('status', 'active'))
            ->orderByDesc('start_date')
            ->limit(300)
            ->get();

        $this->logContractEvent('info', 'contract.create.loaded', $request, [
            'property_id' => $property->id,
            'rentals_count' => $rentals->count(),
            'duration_ms' => $this->millisecondsSince($startedAt),
        ]);

        return view('contracts.create', compact('rentals'));
    }

    public function store(Request $request)
    {
        $startedAt = microtime(true);
        $property = $request->get('current_property');
        abort_unless($property, 404);

        $this->logContractEvent('info', 'contract.store.started', $request, [
            'property_id' => $property->id,
            'disk' => config('filesystems.default'),
            'files' => $this->uploadedFileSummary($request),
        ]);

        $validator = Validator::make($request->all(), [
            'rental_id'             => 'required|exists:rentals,id',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after:start_date',
            'terms'                 => 'nullable|string',
            'file'                  => 'nullable|file|mimes:pdf|max:10240',
            'tenant_id_card_copy'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'paper_contract_images' => 'required|array|min:1|max:20',
            'paper_contract_images.*' => 'required|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        $validator->after(function ($validator) use ($request) {
            $totalBytes = 0;

            foreach (['file', 'tenant_id_card_copy'] as $field) {
                if ($request->hasFile($field)) {
                    $totalBytes += $request->file($field)->getSize();
                }
            }

            foreach ($request->file('paper_contract_images', []) as $image) {
                $totalBytes += $image->getSize();
            }

            if ($totalBytes > 30 * 1024 * 1024) {
                $validator->errors()->add('paper_contract_images', 'ไฟล์รวมทั้งหมดต้องไม่เกิน 30MB เพื่อให้สร้างสัญญาได้รวดเร็ว');
            }
        });

        if ($validator->fails()) {
            $this->logContractEvent('warning', 'contract.store.validation_failed', $request, [
                'property_id' => $property->id,
                'errors' => $validator->errors()->toArray(),
                'files' => $this->uploadedFileSummary($request),
                'duration_ms' => $this->millisecondsSince($startedAt),
            ]);

            return back()
                ->withErrors($validator)
                ->withInput($request->except(['file', 'tenant_id_card_copy', 'paper_contract_images']));
        }

        $data = $validator->validate();

        $rentalStartedAt = microtime(true);
        $rental = Rental::with('room')
            ->where('id', $data['rental_id'])
            ->where('status', 'active')
            ->where(function ($query) use ($property) {
                $query->where('property_id', $property->id)
                    ->orWhereHas('room', fn ($roomQuery) => $roomQuery->where('property_id', $property->id));
            })
            ->first();

        $this->logContractEvent('info', 'contract.store.rental_checked', $request, [
            'property_id' => $property->id,
            'rental_id' => $data['rental_id'],
            'rental_found' => (bool) $rental,
            'duration_ms' => $this->millisecondsSince($rentalStartedAt),
        ]);

        if (!$rental) {
            return back()
                ->withErrors(['rental_id' => 'This rental is not available for a new contract.'])
                ->withInput();
        }

        if (!$rental->property_id) {
            $rental->update(['property_id' => $property->id]);
        }

        try {
            $disk = config('filesystems.default');
            $uploadStartedAt = microtime(true);
            $filePath = null;
            if ($request->hasFile('file')) {
                $singleUploadStartedAt = microtime(true);
                $filePath = $request->file('file')->store('contracts', $disk);
                $this->logContractEvent('info', 'contract.store.file_uploaded', $request, [
                    'field' => 'file',
                    'disk' => $disk,
                    'path' => $filePath,
                    'duration_ms' => $this->millisecondsSince($singleUploadStartedAt),
                ]);
            }

            $singleUploadStartedAt = microtime(true);
            $idCardCopyPath = $request->file('tenant_id_card_copy')
                ->store('contracts/id-cards', $disk);
            $this->logContractEvent('info', 'contract.store.file_uploaded', $request, [
                'field' => 'tenant_id_card_copy',
                'disk' => $disk,
                'path' => $idCardCopyPath,
                'duration_ms' => $this->millisecondsSince($singleUploadStartedAt),
            ]);

            $paperContractImagePaths = [];
            foreach ($request->file('paper_contract_images', []) as $index => $image) {
                $singleUploadStartedAt = microtime(true);
                $paperContractImagePaths[] = $image->store('contracts/paper-originals', $disk);
                $this->logContractEvent('info', 'contract.store.file_uploaded', $request, [
                    'field' => 'paper_contract_images',
                    'index' => $index,
                    'disk' => $disk,
                    'path' => $paperContractImagePaths[array_key_last($paperContractImagePaths)],
                    'duration_ms' => $this->millisecondsSince($singleUploadStartedAt),
                ]);
            }

            $this->logContractEvent('info', 'contract.store.uploads_completed', $request, [
                'disk' => $disk,
                'paper_images_count' => count($paperContractImagePaths),
                'duration_ms' => $this->millisecondsSince($uploadStartedAt),
            ]);
        } catch (\Throwable $e) {
            Log::error('Contract file upload failed', [
                'disk' => config('filesystems.default'),
                'message' => $e->getMessage(),
            ]);
            $this->logContractEvent('error', 'contract.store.upload_failed', $request, [
                'property_id' => $property->id,
                'disk' => config('filesystems.default'),
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'duration_ms' => $this->millisecondsSince($startedAt),
            ], $e);

            return back()
                ->withInput($request->except(['file', 'tenant_id_card_copy', 'paper_contract_images']))
                ->with('error', 'อัปโหลดไฟล์สัญญาไม่สำเร็จ กรุณาตรวจสอบการตั้งค่า Storage/S3 แล้วลองใหม่');
        }

        $contractStartedAt = microtime(true);
        $contract = Contract::create([
            'rental_id'       => $data['rental_id'],
            'property_id'     => $property->id,
            'contract_number' => 'CT-' . strtoupper(Str::random(8)),
            'start_date'      => $data['start_date'],
            'end_date'        => $data['end_date'],
            'terms'           => $data['terms'],
            'file_path'       => $filePath,
            'tenant_id_card_copy_path'  => $idCardCopyPath,
            'paper_contract_image_path' => $paperContractImagePaths[0] ?? null,
            'paper_contract_image_paths' => $paperContractImagePaths,
            'status'          => 'active',
        ]);

        $this->logContractEvent('info', 'contract.store.created', $request, [
            'property_id' => $property->id,
            'rental_id' => $data['rental_id'],
            'contract_id' => $contract->id,
            'db_create_duration_ms' => $this->millisecondsSince($contractStartedAt),
            'total_duration_ms' => $this->millisecondsSince($startedAt),
        ]);

        return redirect()->route('contracts.index')->with('success', 'สร้างสัญญาเรียบร้อย');
    }

    public function show(Contract $contract, Request $request)
    {
        $property = $request->get('current_property');
        abort_if($property && $contract->property_id !== $property->id, 403);
        abort_if(!$property && !$request->user()->isSuperAdmin(), 403);

        $contract->load(['rental.tenant', 'rental.room']);
        return view('contracts.show', compact('contract'));
    }

    public function sign(Request $request, Contract $contract)
    {
        $property = $request->get('current_property');
        abort_if($property && $contract->property_id !== $property->id, 403);
        abort_if(!$property && !$request->user()->isSuperAdmin(), 403);

        $type = $request->validate(['type' => 'required|in:tenant,owner'])['type'];

        if ($type === 'tenant') {
            $contract->update([
                'tenant_signature' => 'signed',
                'tenant_signed_at' => now(),
            ]);
        } else {
            $contract->update([
                'owner_signature' => 'signed',
                'owner_signed_at' => now(),
            ]);
        }

        return back()->with('success', 'บันทึกการลงนามเรียบร้อย');
    }

    public function document(Request $request, Contract $contract, string $type, ?int $index = null)
    {
        $property = $request->get('current_property');
        abort_if($property && $contract->property_id !== $property->id, 403);
        abort_if(!$property && !$request->user()->isSuperAdmin(), 403);

        $path = match ($type) {
            'contract' => $contract->file_path,
            'id-card'  => $contract->tenant_id_card_copy_path,
            'paper'    => $contract->paper_contract_images[$index ?? 0] ?? null,
            default    => null,
        };

        abort_if(!$path, 404);

        $disk = config('filesystems.default');
        $storage = Storage::disk($disk);
        $filename = basename($path);

        if ($disk === 's3') {
            try {
                return redirect()->away($storage->temporaryUrl($path, now()->addMinutes(10), [
                    'ResponseContentDisposition' => 'inline; filename="' . $filename . '"',
                ]));
            } catch (\Throwable) {
                // Fall back to streaming for disks that cannot create temporary URLs.
            }
        }

        return $storage->response($path, $filename, [
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    private function logContractEvent(string $level, string $message, Request $request, array $context = [], ?\Throwable $exception = null): void
    {
        $context = array_merge([
            'route' => $request->route()?->getName(),
            'user_id' => $request->user()?->id,
        ], $context);

        Log::log($level, $message, $context);

        try {
            AppErrorLog::create([
                'level' => $level,
                'exception' => $exception ? $exception::class : null,
                'message' => $message,
                'file' => $exception?->getFile(),
                'line' => $exception?->getLine(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'user_id' => $request->user()?->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'context' => $context,
            ]);
        } catch (\Throwable $logException) {
            Log::error('Failed to write contract event log to database', [
                'message' => $message,
                'exception' => $logException::class,
                'log_error' => $logException->getMessage(),
            ]);
        }
    }

    private function millisecondsSince(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    private function uploadedFileSummary(Request $request): array
    {
        $summary = [];

        foreach (['file', 'tenant_id_card_copy'] as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $summary[$field] = [
                    'name' => $file->getClientOriginalName(),
                    'size_mb' => round($file->getSize() / 1024 / 1024, 2),
                    'mime' => $file->getClientMimeType(),
                ];
            }
        }

        $summary['paper_contract_images'] = collect($request->file('paper_contract_images', []))
            ->map(fn ($file) => [
                'name' => $file->getClientOriginalName(),
                'size_mb' => round($file->getSize() / 1024 / 1024, 2),
                'mime' => $file->getClientMimeType(),
            ])
            ->values()
            ->all();

        return $summary;
    }
}
