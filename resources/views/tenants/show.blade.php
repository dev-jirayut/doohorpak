@extends('layouts.app')
@section('title', $tenant->name)

@section('content')
<div class="page-header">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('tenants.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1>{{ $tenant->name }}</h1>
            <div class="text-muted small">{{ $tenant->phone ?? 'ไม่มีเบอร์โทร' }} · {{ $tenant->email ?? 'ไม่มีอีเมล' }}</div>
        </div>
    </div>
    <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>แก้ไขข้อมูล</a>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card detail-card">
            <div class="card-header">
                <h5 class="title"><span class="icon"><i class="bi bi-person-badge"></i></span>ข้อมูลผู้เช่า</h5>
            </div>
            <div class="card-body p-0">
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt><i class="bi bi-person"></i>ชื่อ</dt>
                        <dd class="value-strong">{{ $tenant->name }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-credit-card-2-front"></i>เลขบัตร</dt>
                        <dd>{{ $tenant->id_card ?? '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-telephone"></i>โทร</dt>
                        <dd>{{ $tenant->phone ?? '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-envelope"></i>อีเมล</dt>
                        <dd>{{ $tenant->email ?? '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-geo-alt"></i>ที่อยู่</dt>
                        <dd>{{ $tenant->address ?? '-' }}</dd>
                    </div>
                    @if($tenant->emergency_contact_name)
                    <div class="detail-row">
                        <dt><i class="bi bi-life-preserver"></i>ฉุกเฉิน</dt>
                        <dd>{{ $tenant->emergency_contact_name }} ({{ $tenant->emergency_contact_phone }})</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">ประวัติการเช่า</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>ห้อง</th><th>เข้าพัก</th><th>ออก</th><th class="text-end">ค่าเช่า</th><th>สถานะ</th></tr></thead>
                    <tbody>
                        @forelse($tenant->rentals as $rental)
                        <tr>
                            <td>{{ $rental->room->room_number }}</td>
                            <td>{{ $rental->start_date->format('d/m/Y') }}</td>
                            <td>{{ $rental->end_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="text-end">{{ number_format($rental->monthly_rent, 0) }}</td>
                            <td><span class="badge bg-{{ $rental->status === 'active' ? 'success' : 'secondary' }}">{{ $rental->status === 'active' ? 'ปัจจุบัน' : 'สิ้นสุดแล้ว' }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">ยังไม่มีประวัติการเช่า</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
