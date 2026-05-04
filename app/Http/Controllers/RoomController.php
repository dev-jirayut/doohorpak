<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $property = $request->get('current_property');
        $query    = Room::with(['roomType', 'activeRental.tenant'])
            ->when($property, fn ($q) => $q->where('property_id', $property->id));

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('floor'))  $query->where('floor', $request->floor);

        $rooms  = $query->orderBy('floor')->orderBy('room_number')->paginate(20);
        $floors = Room::when($property, fn ($q) => $q->where('property_id', $property->id))
            ->distinct()->orderBy('floor')->pluck('floor');

        return view('rooms.index', compact('rooms', 'floors'));
    }

    public function create(Request $request)
    {
        $property = $request->get('current_property');
        $properties = $this->accessibleProperties($request);

        if (! $property && (! $request->user()->isSuperAdmin() || $properties->isEmpty())) {
            return redirect()->route('rooms.index')->with('error', 'กรุณาเลือกหอพักก่อนเพิ่มห้อง');
        }

        $roomTypes = RoomType::orderBy('name')->get();
        return view('rooms.create', compact('roomTypes', 'properties', 'property'));
    }

    public function store(Request $request)
    {
        $property = $request->get('current_property');

        if (! $property) {
            $request->validate([
                'property_id' => ['required', Rule::exists('properties', 'id')],
            ]);

            $property = Property::findOrFail($request->property_id);
            abort_unless($request->user()->canAccessProperty($property->id), 403);
        }

        $request->validate([
            'room_number'  => [
                'required',
                'string',
                'max:20',
                Rule::unique('rooms')->where(fn ($query) => $query->where('property_id', $property->id)),
            ],
            'floor'        => 'required|integer|min:1',
            'room_type_id' => 'required|exists:room_types,id',
            'description'  => 'nullable|string',
        ]);

        Room::create($request->only('room_number', 'floor', 'room_type_id', 'description') + [
            'property_id' => $property->id,
        ]);

        return redirect()->route('rooms.index')->with('success', 'เพิ่มห้องพักสำเร็จ');
    }

    private function accessibleProperties(Request $request)
    {
        $user = $request->user();

        return match (true) {
            $user->isSuperAdmin() => Property::where('is_active', true)->orderBy('name')->get(),
            $user->isOwner()      => $user->ownedProperties()->where('is_active', true)->orderBy('name')->get(),
            default               => $user->properties()->where('is_active', true)->orderBy('name')->get(),
        };
    }

    public function show(Room $room)
    {
        $room->load(['roomType', 'activeRental.tenant', 'rentals.tenant',
            'meterReadings' => fn ($q) => $q->orderByDesc('year')->orderByDesc('month')->limit(12)]);
        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        $roomTypes = RoomType::orderBy('name')->get();
        return view('rooms.edit', compact('room', 'roomTypes'));
    }

    public function update(Request $request, Room $room)
    {
        $request->validate([
            'room_number'  => 'required|string|max:20|unique:rooms,room_number,' . $room->id,
            'floor'        => 'required|integer|min:1',
            'room_type_id' => 'required|exists:room_types,id',
            'status'       => 'required|in:available,occupied,reserved,maintenance',
            'description'  => 'nullable|string',
        ]);

        $room->update($request->only('room_number', 'floor', 'room_type_id', 'status', 'description'));

        return redirect()->route('rooms.show', $room)->with('success', 'แก้ไขห้องพักสำเร็จ');
    }

    public function destroy(Room $room)
    {
        if ($room->rentals()->exists()) {
            return back()->with('error', 'ไม่สามารถลบได้เนื่องจากมีประวัติการเช่าอยู่');
        }
        $room->delete();
        return redirect()->route('rooms.index')->with('success', 'ลบห้องพักสำเร็จ');
    }
}
