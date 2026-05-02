<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChargeController extends Controller
{
    public function index(Request $request): View
    {
        $property = $request->get('current_property');

        $charges = Charge::withCount('rooms')
            ->when($property, fn ($query) => $query->where('property_id', $property->id))
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(15);

        return view('charges.index', compact('charges'));
    }

    public function create(Request $request): View
    {
        $property = $request->get('current_property');
        if (! $property) {
            abort(404);
        }

        $rooms = Room::when($property, fn ($query) => $query->where('property_id', $property->id))
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        return view('charges.create', compact('rooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $property = $request->get('current_property');
        abort_unless($property, 404);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'type'        => 'required|in:monthly,one_time',
            'description' => 'nullable|string',
            'room_ids'    => 'array',
            'room_ids.*'  => 'exists:rooms,id',
            'active_from' => 'required|date',
            'active_to'   => 'nullable|date|after_or_equal:active_from',
        ]);

        $roomIds = collect($request->input('room_ids', []))->filter()->unique()->values();
        if ($roomIds->isNotEmpty()) {
            $validRoomCount = Room::where('property_id', $property->id)
                ->whereIn('id', $roomIds)
                ->count();

            if ($validRoomCount !== $roomIds->count()) {
                return back()->with('error', 'มีห้องที่ไม่ได้อยู่ในหอพักที่เลือก')->withInput();
            }
        }

        $charge = Charge::create([
            'property_id' => $property->id,
            'name'        => $data['name'],
            'amount'      => $data['amount'],
            'type'        => $data['type'],
            'description' => $data['description'] ?? null,
        ]);

        if ($roomIds->isNotEmpty()) {
            $pivot = [];
            foreach ($roomIds as $roomId) {
                $pivot[$roomId] = [
                    'active_from' => $data['active_from'],
                    'active_to'   => $data['active_to'] ?? null,
                ];
            }
            $charge->rooms()->attach($pivot);
        }

        return redirect()->route('charges.index')->with('success', 'เพิ่มรายการค่าใช้จ่ายสำเร็จ');
    }

    public function edit(Request $request, Charge $charge): View
    {
        $property = $request->get('current_property');
        abort_if($property && $charge->property_id !== $property->id, 403);

        $rooms = Room::when($property, fn ($query) => $query->where('property_id', $property->id))
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();
        $assignedRooms = $charge->rooms()->withPivot('active_from', 'active_to')->get();

        return view('charges.edit', compact('charge', 'rooms', 'assignedRooms'));
    }

    public function update(Request $request, Charge $charge): RedirectResponse
    {
        $property = $request->get('current_property');
        abort_if($property && $charge->property_id !== $property->id, 403);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'type'        => 'required|in:monthly,one_time',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $charge->update([
            'name'        => $data['name'],
            'amount'      => $data['amount'],
            'type'        => $data['type'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return redirect()->route('charges.index')->with('success', 'แก้ไขรายการค่าใช้จ่ายสำเร็จ');
    }

    public function destroy(Request $request, Charge $charge): RedirectResponse
    {
        $property = $request->get('current_property');
        abort_if($property && $charge->property_id !== $property->id, 403);

        $charge->delete();

        return redirect()->route('charges.index')->with('success', 'ลบรายการค่าใช้จ่ายสำเร็จ');
    }

    public function assignRoom(Request $request, Charge $charge): RedirectResponse
    {
        $property = $request->get('current_property');
        abort_if($property && $charge->property_id !== $property->id, 403);

        $data = $request->validate([
            'room_id'     => 'required|exists:rooms,id',
            'active_from' => 'required|date',
            'active_to'   => 'nullable|date|after_or_equal:active_from',
        ]);

        if ($property && ! Room::where('property_id', $property->id)->where('id', $data['room_id'])->exists()) {
            return back()->with('error', 'ห้องนี้ไม่ได้อยู่ในหอพักที่เลือก');
        }

        $charge->rooms()->syncWithoutDetaching([
            $data['room_id'] => [
                'active_from' => $data['active_from'],
                'active_to'   => $data['active_to'] ?? null,
            ],
        ]);

        return back()->with('success', 'เพิ่มห้องสำเร็จ');
    }

    public function detachRoom(Request $request, Charge $charge, Room $room): RedirectResponse
    {
        $property = $request->get('current_property');
        abort_if($property && ($charge->property_id !== $property->id || $room->property_id !== $property->id), 403);

        $charge->rooms()->detach($room->id);

        return back()->with('success', 'ลบห้องออกจากรายการสำเร็จ');
    }
}
