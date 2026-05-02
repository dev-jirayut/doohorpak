@extends('layouts.app')
@section('title', 'ใบแจ้งหนี้')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>ใบแจ้งหนี้</h5>
    <a href="{{ route('invoices.generate-form') }}" class="btn btn-success btn-sm">
        <i class="bi bi-plus-lg me-1"></i>ออกใบแจ้งหนี้
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="month" class="form-select form-select-sm">
                    <option value="">ทุกเดือน</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="year" class="form-select form-select-sm">
                    <option value="">ทุกปี</option>
                    @foreach(range(now()->year, now()->year - 3, -1) as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">ทุกสถานะ</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอชำระ</option>
                    <option value="paid"    {{ request('status') == 'paid'    ? 'selected' : '' }}>ชำระแล้ว</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>เกินกำหนด</option>
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
                <tr><th>เลขที่</th><th>ห้อง</th><th>ผู้เช่า</th><th>เดือน</th><th class="text-end">ค่าห้อง</th><th class="text-end">ค่าไฟ</th><th class="text-end">ค่าน้ำ</th><th class="text-end">รวม</th><th>สถานะ</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr>
                    <td><small class="text-muted">{{ $invoice->invoice_number }}</small></td>
                    <td>{{ $invoice->rental->room->room_number }}</td>
                    <td>{{ $invoice->rental->tenant->name }}</td>
                    <td>{{ $invoice->month_name }} {{ $invoice->year }}</td>
                    <td class="text-end">{{ number_format($invoice->room_charge, 0) }}</td>
                    <td class="text-end">{{ number_format($invoice->electricity_charge, 0) }}</td>
                    <td class="text-end">{{ number_format($invoice->water_charge, 0) }}</td>
                    <td class="text-end fw-semibold">{{ number_format($invoice->total_amount, 0) }}</td>
                    <td><span class="badge bg-{{ $invoice->status_badge }}">{{ $invoice->status_label }}</span></td>
                    <td>
                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-file-pdf"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center text-muted py-4">ไม่มีใบแจ้งหนี้</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer">{{ $invoices->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
