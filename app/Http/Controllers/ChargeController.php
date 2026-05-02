<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChargeController extends Controller
{
    public function index(): View
    {
        $charges = Charge::withCount('rooms')->orderBy('type')->orderBy('name')->paginate(15);
        return view('charges.index', compact('charges'));
    }

    public function create(): View
    {
        $rooms = Room::orderBy('floor')->orderBy('room_number')->get();
        return view('charges.create', compact('rooms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'type'        => 'required|in:monthly,one_time',
            'description' => 'nullable|string',
            'room_ids'    => 'array',
            'room_ids.*'  => 'exists:rooms,id',
            'active_from' => 'required|date',
            'active_to'   => 'nullable|date|after_or_equal:active_from',
        ]);

        $charge = Charge::create($request->only('name', 'amount', 'type', 'description'));

        if ($request->filled('room_ids')) {
            $pivot = [];
            foreach ($request->room_ids as $roomId) {
                $pivot[$roomId] = [
                    'active_from' => $request->active_from,
                    'active_to'   => $request->active_to,
                ];
            }
            $charge->rooms()->attach($pivot);
        }

        return redirect()->route('charges.index')->with('success', 'เพิ่มรายการค่าใช้จ่ายสำเร็จ');
    }

    public function edit(Charge $charge): View
    {
        $rooms           = Room::orderBy('floor')->orderBy('room_number')->get();
        $assignedRooms   = $charge->rooms()->withPivot('active_from', 'active_to')->get();
        return view('charges.edit', compact('charge', 'rooms', 'assignedRooms'));
    }

    public function update(Request $request, Charge $charge): RedirectResponse
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'type'        => 'required|in:monthly,one_time',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $charge->update([
            'name'        => $request->name,
            'amount'      => $request->amount,
            'type'        => $request->type,
            'description' => $request->description,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return redirect()->route('charges.index')->with('success', 'แก้ไขรายการค่าใช้จ่ายสำเร็จ');
    }

    public function destroy(Charge $charge): RedirectResponse
    {
        $charge->delete();
        return redirect()->route('charges.index')->with('success', 'ลบรายการค่าใช้จ่ายสำเร็จ');
    }

    public function assignRoom(Request $request, Charge $charge): RedirectResponse
    {
        $request->validate([
            'room_id'     => 'required|exists:rooms,id',
            'active_from' => 'required|date',
            'active_to'   => 'nullable|date|after_or_equal:active_from',
        ]);

        $charge->rooms()->attach($request->room_id, [
            'active_from' => $request->active_from,
            'active_to'   => $request->active_to,
        ]);

        return back()->with('success', 'เพิ่มห้องสำเร็จ');
    }

    public function detachRoom(Charge $charge, Room $room): RedirectResponse
    {
        $charge->rooms()->detach($room->id);
        return back()->with('success', 'ลบห้องออกจากรายการสำเร็จ');
    }
}
