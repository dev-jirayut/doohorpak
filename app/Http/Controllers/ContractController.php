<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        $property = $request->get('current_property');
        if (!$property) {
            return redirect()->route('dashboard')->with('error', 'กรุณาเลือกหอพักก่อนสร้างสัญญา');
        }

        $rentals  = Rental::with(['tenant', 'room'])
            ->where('property_id', $property->id)
            ->where('status', 'active')
            ->whereDoesntHave('contract', fn($q) => $q->whereIn('status', ['active']))
            ->get();

        return view('contracts.create', compact('rentals'));
    }

    public function store(Request $request)
    {
        $property = $request->get('current_property');
        abort_unless($property, 404);

        $data = $request->validate([
            'rental_id'             => 'required|exists:rentals,id',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after:start_date',
            'terms'                 => 'nullable|string',
            'file'                  => 'nullable|file|mimes:pdf|max:10240',
            'tenant_id_card_copy'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'paper_contract_images' => 'required|array|min:1|max:20',
            'paper_contract_images.*' => 'required|image|mimes:jpg,jpeg,png|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('contracts', config('filesystems.default'));
        }

        $idCardCopyPath = $request->file('tenant_id_card_copy')
            ->store('contracts/id-cards', config('filesystems.default'));
        $paperContractImagePaths = [];
        foreach ($request->file('paper_contract_images', []) as $image) {
            $paperContractImagePaths[] = $image->store('contracts/paper-originals', config('filesystems.default'));
        }

        Contract::create([
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
}
