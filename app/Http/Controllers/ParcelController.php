<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Models\Room;
use App\Services\LineService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ParcelController extends Controller
{
    public function index(Request $request)
    {
        $property = $request->get('current_property');

        $query = Parcel::with(['room', 'tenant'])
            ->when($property, fn ($q) => $q->where('property_id', $property->id))
            ->latest('received_at');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $parcels       = $query->paginate(25);
        $waitingCount  = Parcel::when($property, fn ($q) => $q->where('property_id', $property->id))->where('status', 'waiting')->count();
        $notifiedCount = Parcel::when($property, fn ($q) => $q->where('property_id', $property->id))->where('status', 'notified')->count();

        return view('parcels.index', compact('parcels', 'waitingCount', 'notifiedCount'));
    }

    public function create(Request $request)
    {
        $property = $request->get('current_property');
        if (!$property) {
            return redirect()->route('parcels.index')->with('error', 'กรุณาเลือกหอพักก่อนบันทึกพัสดุ');
        }

        $rooms    = Room::where('property_id', $property->id)
            ->where('status', 'occupied')
            ->with('activeRental.tenant')
            ->orderBy('room_number')
            ->get();

        return view('parcels.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $property = $request->get('current_property');
        abort_unless($property, 404);

        $data = $request->validate([
            'room_id'         => 'required|exists:rooms,id',
            'type'            => 'required|in:parcel,letter,document,food',
            'sender'          => 'nullable|string|max:100',
            'carrier'         => 'nullable|string|max:50',
            'tracking_number' => 'nullable|string|max:50',
            'description'     => 'nullable|string',
            'received_by'     => 'nullable|string|max:50',
            'note'            => 'nullable|string',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'video'           => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska|max:10240',
        ]);

        $room   = Room::with('activeRental.tenant')->find($data['room_id']);
        abort_unless($room && $room->property_id === $property->id, 403);
        $tenant = $room->activeRental?->tenant;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('parcels', config('filesystems.default'));
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('parcels/videos', config('filesystems.default'));
        }

        $parcel = Parcel::create([
            'parcel_number'   => 'PKG-' . strtoupper(Str::random(8)),
            'property_id'     => $property->id,
            'room_id'         => $data['room_id'],
            'tenant_id'       => $tenant?->id,
            'type'            => $data['type'],
            'sender'          => $data['sender'],
            'carrier'         => $data['carrier'],
            'tracking_number' => $data['tracking_number'],
            'description'     => $data['description'],
            'received_by'     => $data['received_by'] ?? auth()->user()->name,
            'received_at'     => now(),
            'status'          => 'waiting',
            'image_path'      => $imagePath,
            'video_path'      => $videoPath,
            'note'            => $data['note'],
        ]);

        // Auto notify via LINE
        $this->notifyTenant($parcel, app(LineService::class));

        return redirect()->route('parcels.index')->with('success', 'บันทึกพัสดุเรียบร้อย และแจ้งผู้เช่าทาง LINE แล้ว');
    }

    public function markCollected(Request $request, Parcel $parcel)
    {
        $property = $request->get('current_property');
        abort_if($property && $parcel->property_id !== $property->id, 403);
        abort_if(!$property && !$request->user()->isSuperAdmin(), 403);

        $parcel->update([
            'status'       => 'collected',
            'collected_at' => now(),
        ]);

        return back()->with('success', 'บันทึกการรับพัสดุเรียบร้อย');
    }

    public function resendNotify(Request $request, Parcel $parcel)
    {
        $property = $request->get('current_property');
        abort_if($property && $parcel->property_id !== $property->id, 403);
        abort_if(!$property && !$request->user()->isSuperAdmin(), 403);

        $this->notifyTenant($parcel, app(LineService::class));

        return back()->with('success', 'ส่งการแจ้งเตือนซ้ำเรียบร้อย');
    }

    private function notifyTenant(Parcel $parcel, LineService $line): void
    {
        $property = $parcel->property;
        $tenant   = $parcel->tenant;
        $room     = $parcel->room;
        $lineId   = $tenant?->line_user_id ?? $tenant?->user?->line_user_id;

        $icon = $parcel->type_icon;
        $type = $parcel->type_label;

        // Flex message to tenant
        if ($lineId) {
            $flex = [
                'type' => 'bubble',
                'header' => [
                    'type' => 'box', 'layout' => 'vertical',
                    'backgroundColor' => '#002C2C',
                    'contents' => [
                        ['type' => 'text', 'text' => "{$icon} มี{$type}มาถึงแล้ว!", 'color' => '#A1FFD1', 'size' => 'lg', 'weight' => 'bold'],
                        ['type' => 'text', 'text' => "ห้อง {$room->room_number}", 'color' => 'rgba(255,255,255,0.7)', 'size' => 'sm'],
                    ],
                ],
                'body' => [
                    'type' => 'box', 'layout' => 'vertical', 'spacing' => 'md',
                    'contents' => array_filter([
                        $parcel->sender        ? ['type'=>'box','layout'=>'horizontal','contents'=>[['type'=>'text','text'=>'ผู้ส่ง','color'=>'#888','size'=>'sm','flex'=>3],['type'=>'text','text'=>$parcel->sender,'color'=>'#002C2C','size'=>'sm','flex'=>5,'align'=>'end']]] : null,
                        $parcel->carrier       ? ['type'=>'box','layout'=>'horizontal','contents'=>[['type'=>'text','text'=>'ขนส่ง','color'=>'#888','size'=>'sm','flex'=>3],['type'=>'text','text'=>$parcel->carrier,'color'=>'#002C2C','size'=>'sm','flex'=>5,'align'=>'end']]] : null,
                        $parcel->tracking_number ? ['type'=>'box','layout'=>'horizontal','contents'=>[['type'=>'text','text'=>'เลขพัสดุ','color'=>'#888','size'=>'sm','flex'=>3],['type'=>'text','text'=>$parcel->tracking_number,'color'=>'#002C2C','size'=>'sm','flex'=>5,'align'=>'end','wrap'=>true]]] : null,
                        ['type'=>'separator'],
                        ['type'=>'text','text'=>"กรุณารับ{$type}ที่เคาน์เตอร์ ขอบคุณครับ/ค่ะ",'wrap'=>true,'size'=>'sm','color'=>'#666'],
                    ]),
                ],
            ];
            $line->pushFlexMessage($lineId, "มี{$type}มาถึง ห้อง {$room->room_number}", $flex, $property);
        }

        // Also notify admin/owner via LINE Messaging API
        $tenantName = $tenant?->name ?? 'ไม่ระบุ';
        $line->notifyOwner($property, "\n{$icon} {$type}มาถึง\nห้อง: {$room->room_number}\nผู้รับ: {$tenantName}" .
            ($parcel->carrier ? "\nขนส่ง: {$parcel->carrier}" : '') .
            ($parcel->tracking_number ? "\nเลขพัสดุ: {$parcel->tracking_number}" : ''));

        $parcel->update(['status' => 'notified', 'notified_at' => now()]);
    }
}
