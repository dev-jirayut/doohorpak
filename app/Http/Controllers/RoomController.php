<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

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

    public function create()
    {
        $roomTypes = RoomType::orderBy('name')->get();
        return view('rooms.create', compact('roomTypes'));
    }

    public function store(Request $request)
    {
        $property = $request->get('current_property');

        $request->validate([
            'room_number'  => 'required|string|max:20|unique:rooms',
            'floor'        => 'required|integer|min:1',
            'room_type_id' => 'required|exists:room_types,id',
            'description'  => 'nullable|string',
        ]);

        Room::create($request->only('room_number', 'floor', 'room_type_id', 'description') + [
            'property_id' => $property?->id,
        ]);

        return redirect()->route('rooms.index')->with('success', 'เพิ่มห้องพักสำเร็จ');
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
