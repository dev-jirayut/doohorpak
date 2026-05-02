@extends('layouts.app')
@section('title', 'สัญญาเช่า')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>สัญญาเช่า</h5>
    <a href="{{ route('rentals.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>เปิดสัญญาเช่าใหม่
    </a>
</div>
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">ทุกสถานะ</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>ปัจจุบัน</option>
                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>สิ้นสุดแล้ว</option>
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
                <tr><th>ห้อง</th><th>ผู้เช่า</th><th class="text-end">ค่าเช่า/เดือน</th><th class="text-end">ค่ามัดจำ</th><th>เข้าพัก</th><th>ออก</th><th>สถานะ</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($rentals as $rental)
                <tr>
                    <td class="fw-semibold">{{ $rental->room->room_number }}</td>
                    <td>{{ $rental->tenant->name }}</td>
                    <td class="text-end">{{ number_format($rental->monthly_rent, 0) }}</td>
                    <td class="text-end">{{ number_format($rental->deposit_amount, 0) }}</td>
                    <td>{{ $rental->start_date->format('d/m/Y') }}</td>
                    <td>{{ $rental->end_date?->format('d/m/Y') ?? '-' }}</td>
                    <td><span class="badge bg-{{ $rental->status === 'active' ? 'success' : 'secondary' }}">{{ $rental->status === 'active' ? 'ปัจจุบัน' : 'สิ้นสุดแล้ว' }}</span></td>
                    <td><a href="{{ route('rentals.show', $rental) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">ยังไม่มีสัญญาเช่า</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($rentals->hasPages())
    <div class="card-footer">{{ $rentals->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
