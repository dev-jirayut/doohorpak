<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function index(Request $request)
    {
        $property = $request->get('current_property');

        $query = Rental::with(['room.roomType', 'tenant'])
            ->when($property, function ($query) use ($property) {
                $query->where(function ($propertyQuery) use ($property) {
                    $propertyQuery->where('property_id', $property->id)
                        ->orWhereHas('room', fn ($roomQuery) => $roomQuery->where('property_id', $property->id));
                });
            });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rentals = $query->orderByDesc('start_date')->paginate(20);

        return view('rentals.index', compact('rentals'));
    }

    public function create(Request $request)
    {
        $property = $request->get('current_property');

        $rooms = Room::where('status', 'available')
            ->when($property, fn ($query) => $query->where('property_id', $property->id))
            ->with('roomType')
            ->orderBy('room_number')
            ->get();

        $tenants = Tenant::when($property, fn ($query) => $query->where('property_id', $property->id))
            ->orderBy('name')
            ->get();

        return view('rentals.create', compact('rooms', 'tenants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_id'        => 'required|exists:rooms,id',
            'tenant_id'      => 'required|exists:tenants,id',
            'monthly_rent'   => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'note'           => 'nullable|string',
        ]);

        $property = $request->get('current_property');
        $room = Room::findOrFail($request->room_id);

        if ($property && $room->property_id !== $property->id) {
            return back()->with('error', 'ห้องนี้ไม่ได้อยู่ในหอพักที่เลือก')->withInput();
        }

        if ($room->status !== 'available') {
            return back()->with('error', 'ห้องนี้ไม่ว่าง')->withInput();
        }

        Rental::create(array_merge(
            $request->only('room_id', 'tenant_id', 'monthly_rent', 'deposit_amount', 'start_date', 'note'),
            [
                'property_id' => $property?->id ?? $room->property_id,
                'status' => 'active',
            ],
        ));

        $room->update(['status' => 'occupied']);

        return redirect()->route('rentals.index')
            ->with('success', 'เปิดสัญญาเช่าสำเร็จ');
    }

    public function show(Rental $rental)
    {
        $rental->load(['room.roomType', 'tenant', 'invoices']);

        return view('rentals.show', compact('rental'));
    }

    public function terminate(Request $request, Rental $rental)
    {
        $request->validate([
            'end_date' => 'required|date|after_or_equal:' . $rental->start_date->format('Y-m-d'),
        ]);

        $rental->update([
            'status'   => 'terminated',
            'end_date' => $request->end_date,
        ]);

        $rental->room->update(['status' => 'available']);

        return redirect()->route('rentals.index')
            ->with('success', 'ปิดสัญญาเช่าสำเร็จ');
    }
}
