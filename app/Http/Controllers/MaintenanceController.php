<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\User;
use App\Services\LineService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $property = $request->get('current_property');

        $query = MaintenanceRequest::with(['room', 'tenant', 'assignedTo'])
            ->when($property, fn ($q) => $q->where('property_id', $property->id))
            ->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        $requests = $query->paginate(20);

        return view('maintenance.index', compact('requests'));
    }

    public function create(Request $request)
    {
        $property = $request->get('current_property');
        if (!$property) {
            return redirect()->route('maintenance.index')->with('error', 'กรุณาเลือกหอพักก่อนสร้างรายการแจ้งซ่อม');
        }

        $rooms    = Room::where('property_id', $property->id)->orderBy('room_number')->get();

        return view('maintenance.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $property = $request->get('current_property');
        abort_unless($property, 404);

        $data = $request->validate([
            'room_id'     => 'required|exists:rooms,id',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category'    => 'required|in:general,electrical,plumbing,furniture,other',
            'priority'    => 'required|in:low,normal,high,urgent',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'video'       => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska|max:51200',
        ]);

        $room = Room::with('activeRental.tenant')
            ->where('property_id', $property->id)
            ->findOrFail($data['room_id']);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('maintenance', config('filesystems.default'));
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('maintenance/videos', config('filesystems.default'));
        }

        $maintenance = MaintenanceRequest::create([
            'request_number' => 'MR-' . strtoupper(Str::random(8)),
            'property_id'    => $property->id,
            'room_id'        => $data['room_id'],
            'tenant_id'      => $room->activeRental?->tenant?->id,
            'title'          => $data['title'],
            'description'    => $data['description'],
            'category'       => $data['category'],
            'priority'       => $data['priority'],
            'status'         => 'pending',
            'image_path'     => $imagePath,
            'video_path'     => $videoPath,
        ]);

        app(LineService::class)->sendMaintenanceUpdate($maintenance);

        return redirect()->route('maintenance.show', $maintenance)->with('success', 'บันทึกคำขอซ่อมเรียบร้อย');
    }

    public function show(MaintenanceRequest $maintenance, Request $request)
    {
        $property = $request->get('current_property');
        abort_if($property && $maintenance->property_id !== $property->id, 403);
        abort_if(!$property && !$request->user()->isSuperAdmin(), 403);

        $maintenance->load(['room', 'tenant', 'assignedTo']);
        $staff = User::whereIn('role', ['owner', 'staff'])
            ->when($property, fn ($q) => $q
                ->whereHas('properties', fn($qp) => $qp->where('properties.id', $property->id))
                ->orWhere('id', $property->owner_id)
            )
            ->get();

        return view('maintenance.show', compact('maintenance', 'staff'));
    }

    public function update(Request $request, MaintenanceRequest $maintenance)
    {
        $property = $request->get('current_property');
        abort_if($property && $maintenance->property_id !== $property->id, 403);
        abort_if(!$property && !$request->user()->isSuperAdmin(), 403);

        $data = $request->validate([
            'status'          => 'required|in:pending,in_progress,done,cancelled',
            'assigned_to'     => 'nullable|exists:users,id',
            'technician_note' => 'nullable|string',
        ]);

        $wasNotDone = $maintenance->status !== 'done';

        $maintenance->update([
            'status'          => $data['status'],
            'assigned_to'     => $data['assigned_to'] ?? $maintenance->assigned_to,
            'technician_note' => $data['technician_note'] ?? $maintenance->technician_note,
            'resolved_at'     => $data['status'] === 'done' && $wasNotDone ? now() : $maintenance->resolved_at,
        ]);

        app(LineService::class)->sendMaintenanceUpdate($maintenance->fresh());

        return back()->with('success', 'อัปเดตสถานะเรียบร้อย');
    }
}
