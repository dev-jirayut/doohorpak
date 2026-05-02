@extends('layouts.app')
@section('title', 'ห้อง ' . $room->room_number)

@section('content')
<div class="page-header">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('rooms.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1>ห้อง {{ $room->room_number }}</h1>
            <div class="text-muted small">ชั้น {{ $room->floor }} · {{ $room->roomType->name }}</div>
        </div>
    </div>
    <span class="badge bg-{{ $room->status_badge }}">{{ $room->status_label }}</span>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card detail-card">
            <div class="card-header">
                <h5 class="title"><span class="icon"><i class="bi bi-door-open"></i></span>ข้อมูลห้อง</h5>
            </div>
            <div class="card-body p-0">
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt><i class="bi bi-hash"></i>เลขห้อง</dt>
                        <dd class="value-strong">{{ $room->room_number }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-layers"></i>ชั้น</dt>
                        <dd>{{ $room->floor }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-tag"></i>ประเภท</dt>
                        <dd>{{ $room->roomType->name }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-cash-stack"></i>ค่าห้อง/เดือน</dt>
                        <dd class="value-strong">{{ number_format($room->roomType->base_price, 0) }} บาท</dd>
                    </div>
                    @if($room->description)
                    <div class="detail-row">
                        <dt><i class="bi bi-journal-text"></i>รายละเอียด</dt>
                        <dd>{{ $room->description }}</dd>
                    </div>
                    @endif
                </dl>
                <div style="padding:0 1.25rem 1.25rem" class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil me-1"></i>แก้ไข
                    </a>
                    @if($room->status === 'available')
                    <a href="{{ route('rentals.create', ['room_id' => $room->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-person-plus me-1"></i>เปิดสัญญาเช่า
                    </a>
                    @endif
                </div>
            </div>
        </div>

        @if($room->activeRental)
        <div class="card detail-card mt-3">
            <div class="card-header">
                <h5 class="title"><span class="icon"><i class="bi bi-person-fill"></i></span>ผู้เช่าปัจจุบัน</h5>
            </div>
            <div class="card-body p-0">
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt><i class="bi bi-person"></i>ชื่อ</dt>
                        <dd class="value-strong">{{ $room->activeRental->tenant->name }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-telephone"></i>โทร</dt>
                        <dd>{{ $room->activeRental->tenant->phone ?? '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-calendar-check"></i>เข้าพัก</dt>
                        <dd>{{ $room->activeRental->start_date->format('d/m/Y') }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-cash"></i>ค่าเช่า</dt>
                        <dd class="value-strong">{{ number_format($room->activeRental->monthly_rent, 0) }} บ./เดือน</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-shield-check"></i>ค่ามัดจำ</dt>
                        <dd>{{ number_format($room->activeRental->deposit_amount, 0) }} บาท</dd>
                    </div>
                </dl>
                <div style="padding:0 1.25rem 1.25rem">
                    <a href="{{ route('rentals.show', $room->activeRental) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-text me-1"></i>ดูสัญญาเช่า
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-lightning-charge me-2"></i>บันทึกมิเตอร์ล่าสุด</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>เดือน/ปี</th>
                            <th class="text-end">ไฟ (หน่วย)</th>
                            <th class="text-end">น้ำ (หน่วย)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($room->meterReadings as $reading)
                        <tr>
                            <td>{{ $reading->month }}/{{ $reading->year }}</td>
                            <td class="text-end">{{ number_format($reading->electricity_units, 2) }}</td>
                            <td class="text-end">{{ number_format($reading->water_units, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">ยังไม่มีข้อมูลมิเตอร์</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
