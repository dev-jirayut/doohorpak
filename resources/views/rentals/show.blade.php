@extends('layouts.app')
@section('title', 'สัญญาเช่า')

@section('content')
<div class="page-header">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('rentals.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1>สัญญาเช่า ห้อง {{ $rental->room->room_number }}</h1>
            <div class="text-muted small">{{ $rental->tenant->name }} · เริ่ม {{ $rental->start_date->format('d/m/Y') }}</div>
        </div>
    </div>
    <span class="badge bg-{{ $rental->status === 'active' ? 'success' : 'secondary' }}">
        {{ $rental->status === 'active' ? 'ปัจจุบัน' : 'สิ้นสุดแล้ว' }}
    </span>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card detail-card">
            <div class="card-header">
                <h5 class="title"><span class="icon"><i class="bi bi-file-earmark-text"></i></span>รายละเอียดสัญญา</h5>
            </div>
            <div class="card-body p-0">
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt><i class="bi bi-door-open"></i>ห้องพัก</dt>
                        <dd class="value-strong">{{ $rental->room->room_number }} ({{ $rental->room->roomType->name }})</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-person"></i>ผู้เช่า</dt>
                        <dd>{{ $rental->tenant->name }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-telephone"></i>โทร</dt>
                        <dd>{{ $rental->tenant->phone ?? '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-calendar-check"></i>วันเข้าพัก</dt>
                        <dd>{{ $rental->start_date->format('d/m/Y') }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-calendar-x"></i>วันออก</dt>
                        <dd>{{ $rental->end_date?->format('d/m/Y') ?? '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-cash-stack"></i>ค่าเช่า/เดือน</dt>
                        <dd class="value-strong">{{ number_format($rental->monthly_rent, 2) }} บาท</dd>
                    </div>
                    <div class="detail-row">
                        <dt><i class="bi bi-shield-check"></i>ค่ามัดจำ</dt>
                        <dd>{{ number_format($rental->deposit_amount, 2) }} บาท</dd>
                    </div>
                    @if($rental->note)
                    <div class="detail-row">
                        <dt><i class="bi bi-journal-text"></i>หมายเหตุ</dt>
                        <dd>{{ $rental->note }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        @if($rental->status === 'active')
        <div class="card action-card mt-3">
            <div class="card-header text-warning"><i class="bi bi-x-circle me-2"></i>ปิดสัญญาเช่า</div>
            <div class="card-body">
                <form method="POST" action="{{ route('rentals.terminate', $rental) }}" onsubmit="return confirm('ยืนยันการปิดสัญญาเช่า?')">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">วันที่ออก <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                            value="{{ old('end_date', date('Y-m-d')) }}">
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-warning"><i class="bi bi-x-lg me-1"></i>ปิดสัญญาเช่า</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-2"></i>ใบแจ้งหนี้</span>
                <a href="{{ route('invoices.index', ['rental_id' => $rental->id]) }}" class="btn btn-sm btn-outline-secondary">ดูทั้งหมด</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>เลขที่</th><th>เดือน</th><th class="text-end">ยอด</th><th>สถานะ</th><th></th></tr></thead>
                    <tbody>
                        @forelse($rental->invoices->take(10) as $invoice)
                        <tr>
                            <td><small>{{ $invoice->invoice_number }}</small></td>
                            <td>{{ $invoice->month_name }} {{ $invoice->year }}</td>
                            <td class="text-end">{{ number_format($invoice->total_amount, 0) }}</td>
                            <td><span class="badge bg-{{ $invoice->status_badge }}">{{ $invoice->status_label }}</span></td>
                            <td><a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">ยังไม่มีใบแจ้งหนี้</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
