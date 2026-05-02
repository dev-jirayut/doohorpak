@extends('layouts.app')
@section('title', 'บันทึกมิเตอร์น้ำ/ไฟ')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>บันทึกมิเตอร์น้ำ/ไฟ</h5>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label form-label-sm mb-1">เดือน</label>
                <select name="month" class="form-select form-select-sm">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label form-label-sm mb-1">ปี</label>
                <select name="year" class="form-select form-select-sm">
                    @foreach(range(now()->year, now()->year - 3, -1) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-secondary"><i class="bi bi-search me-1"></i>ค้นหา</button>
            </div>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('meter-readings.store') }}">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="year" value="{{ $year }}">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>มิเตอร์ประจำเดือน {{ $month }}/{{ $year }}</span>
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-save me-1"></i>บันทึกทั้งหมด</button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:90px">ห้อง</th>
                        <th>ผู้เช่า</th>
                        <th colspan="2" class="text-center bg-warning bg-opacity-10">
                            <i class="bi bi-lightning-charge text-warning me-1"></i>ค่าไฟ (หน่วย)
                        </th>
                        <th colspan="2" class="text-center bg-info bg-opacity-10">
                            <i class="bi bi-droplet text-info me-1"></i>ค่าน้ำ (หน่วย)
                        </th>
                        <th class="text-center">หน่วยรวม</th>
                        <th style="width:120px">หมายเหตุ</th>
                    </tr>
                    <tr class="small text-muted">
                        <th></th><th></th>
                        <th class="text-center bg-warning bg-opacity-10">มิเตอร์เดิม</th>
                        <th class="text-center bg-warning bg-opacity-10">มิเตอร์ใหม่</th>
                        <th class="text-center bg-info bg-opacity-10">มิเตอร์เดิม</th>
                        <th class="text-center bg-info bg-opacity-10">มิเตอร์ใหม่</th>
                        <th class="text-center"><small>ไฟ / น้ำ</small></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($occupiedRooms as $i => $room)
                    @php
                        $reading  = $room->meterReadings->first();
                        $prevRead = $previousReadings->get($room->id);
                        $elecPrev = old("readings.{$i}.electricity_previous", $reading?->electricity_previous ?? $prevRead?->electricity_current ?? 0);
                        $elecCurr = old("readings.{$i}.electricity_current", $reading?->electricity_current ?? 0);
                        $watPrev  = old("readings.{$i}.water_previous", $reading?->water_previous ?? $prevRead?->water_current ?? 0);
                        $watCurr  = old("readings.{$i}.water_current", $reading?->water_current ?? 0);
                    @endphp
                    <input type="hidden" name="readings[{{ $i }}][room_id]" value="{{ $room->id }}">
                    <tr>
                        <td class="fw-bold">{{ $room->room_number }}</td>
                        <td class="small">{{ $room->activeRental?->tenant?->name ?? '-' }}</td>
                        <td class="bg-warning bg-opacity-10">
                            <input type="number" name="readings[{{ $i }}][electricity_previous]"
                                class="form-control form-control-sm text-center"
                                value="{{ $elecPrev }}" min="0" step="0.01" style="width:100px">
                        </td>
                        <td class="bg-warning bg-opacity-10">
                            <input type="number" name="readings[{{ $i }}][electricity_current]"
                                class="form-control form-control-sm text-center elec-curr"
                                value="{{ $elecCurr }}" min="0" step="0.01" style="width:100px"
                                data-row="{{ $i }}">
                        </td>
                        <td class="bg-info bg-opacity-10">
                            <input type="number" name="readings[{{ $i }}][water_previous]"
                                class="form-control form-control-sm text-center"
                                value="{{ $watPrev }}" min="0" step="0.01" style="width:100px">
                        </td>
                        <td class="bg-info bg-opacity-10">
                            <input type="number" name="readings[{{ $i }}][water_current]"
                                class="form-control form-control-sm text-center wat-curr"
                                value="{{ $watCurr }}" min="0" step="0.01" style="width:100px"
                                data-row="{{ $i }}">
                        </td>
                        <td class="text-center small" id="summary-{{ $i }}">
                            @php
                                $eU = max(0, $elecCurr - $elecPrev);
                                $wU = max(0, $watCurr - $watPrev);
                            @endphp
                            <span class="text-warning">{{ number_format($eU, 2) }}</span>
                            / <span class="text-info">{{ number_format($wU, 2) }}</span>
                        </td>
                        <td>
                            <input type="text" name="readings[{{ $i }}][note]"
                                class="form-control form-control-sm"
                                value="{{ old("readings.{$i}.note", $reading?->note) }}"
                                placeholder="หมายเหตุ">
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">ไม่มีห้องที่มีผู้เช่า</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($occupiedRooms->isNotEmpty())
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึกมิเตอร์ทั้งหมด</button>
        </div>
        @endif
    </div>
</form>
@endsection
