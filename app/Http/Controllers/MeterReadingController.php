<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\Room;
use Illuminate\Http\Request;

class MeterReadingController extends Controller
{
    public function index(Request $request)
    {
        $property = $request->get('current_property');
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        $occupiedRooms = Room::where('status', 'occupied')
            ->when($property, fn ($query) => $query->where('property_id', $property->id))
            ->with(['roomType', 'meterReadings' => fn ($q) => $q->where('month', $month)->where('year', $year)])
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        $previousMonth = $month === 1 ? 12 : $month - 1;
        $previousYear  = $month === 1 ? $year - 1 : $year;

        $previousReadings = MeterReading::where('month', $previousMonth)
            ->where('year', $previousYear)
            ->whereIn('room_id', $occupiedRooms->pluck('id'))
            ->get()->keyBy('room_id');

        return view('meter-readings.index', compact(
            'occupiedRooms', 'previousReadings', 'month', 'year'
        ));
    }

    public function store(Request $request)
    {
        $property = $request->get('current_property');

        $request->validate([
            'month'     => 'required|integer|between:1,12',
            'year'      => 'required|integer|min:2020',
            'readings'  => 'required|array',
            'readings.*.room_id'              => 'required|exists:rooms,id',
            'readings.*.electricity_previous' => 'required|numeric|min:0',
            'readings.*.electricity_current'  => 'required|numeric|min:0',
            'readings.*.water_previous'       => 'required|numeric|min:0',
            'readings.*.water_current'        => 'required|numeric|min:0',
        ]);

        $roomIds = collect($request->readings)->pluck('room_id')->filter()->unique()->values();
        if ($property) {
            $validRoomCount = Room::where('property_id', $property->id)
                ->whereIn('id', $roomIds)
                ->count();

            if ($validRoomCount !== $roomIds->count()) {
                return back()->with('error', 'มีห้องที่ไม่ได้อยู่ในหอพักที่เลือก')->withInput();
            }
        }

        foreach ($request->readings as $data) {
            MeterReading::updateOrCreate(
                ['room_id' => $data['room_id'], 'month' => $request->month, 'year' => $request->year],
                [
                    'electricity_previous' => $data['electricity_previous'],
                    'electricity_current'  => $data['electricity_current'],
                    'water_previous'       => $data['water_previous'],
                    'water_current'        => $data['water_current'],
                    'note'                 => $data['note'] ?? null,
                ]
            );
        }

        return redirect()->route('meter-readings.index', ['month' => $request->month, 'year' => $request->year])
            ->with('success', 'บันทึกมิเตอร์สำเร็จ');
    }
}
