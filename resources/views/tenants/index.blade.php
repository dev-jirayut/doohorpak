@extends('layouts.app')
@section('title', 'ผู้เช่าทั้งหมด')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-people me-2"></i>ผู้เช่าทั้งหมด</h5>
    <a href="{{ route('tenants.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>เพิ่มผู้เช่า
    </a>
</div>
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2">
            <div class="col">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหาชื่อ, โทร, เลขบัตร..." value="{{ request('search') }}">
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
                <tr><th>ชื่อ</th><th>เลขบัตร</th><th>โทรศัพท์</th><th>ห้องปัจจุบัน</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                <tr>
                    <td class="fw-semibold">{{ $tenant->name }}</td>
                    <td>{{ $tenant->id_card ?? '-' }}</td>
                    <td>{{ $tenant->phone ?? '-' }}</td>
                    <td>
                        @if($tenant->activeRental)
                            <span class="badge bg-danger">ห้อง {{ $tenant->activeRental->room->room_number }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีผู้เช่า</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tenants->hasPages())
    <div class="card-footer">{{ $tenants->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
