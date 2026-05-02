@extends('layouts.app')
@section('title', 'ห้องพักทั้งหมด')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-door-open me-2"></i>ห้องพักทั้งหมด</h5>
    <a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>เพิ่มห้องพัก
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">ทุกสถานะ</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>ว่าง</option>
                    <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>มีผู้เช่า</option>
                    <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>จองแล้ว</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>ซ่อมบำรุง</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="floor" class="form-select form-select-sm">
                    <option value="">ทุกชั้น</option>
                    @foreach($floors as $floor)
                        <option value="{{ $floor }}" {{ request('floor') == $floor ? 'selected' : '' }}>ชั้น {{ $floor }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-secondary"><i class="bi bi-search me-1"></i>ค้นหา</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>เลขห้อง</th>
                    <th>ชั้น</th>
                    <th>ประเภท</th>
                    <th class="text-end">ค่าห้อง</th>
                    <th>สถานะ</th>
                    <th>ผู้เช่าปัจจุบัน</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                <tr>
                    <td class="fw-semibold">{{ $room->room_number }}</td>
                    <td>{{ $room->floor }}</td>
                    <td>{{ $room->roomType->name }}</td>
                    <td class="text-end">{{ number_format($room->roomType->base_price, 0) }}</td>
                    <td><span class="badge bg-{{ $room->status_badge }}">{{ $room->status_label }}</span></td>
                    <td>{{ $room->activeRental?->tenant?->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('rooms.show', $room) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">ยังไม่มีห้องพัก</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rooms->hasPages())
    <div class="card-footer">{{ $rooms->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
