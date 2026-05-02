@extends('layouts.app')
@section('title', 'ใบแจ้งหนี้ ' . $invoice->invoice_number)

@section('content')
<div class="d-flex align-items-center mb-3 gap-2">
    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">ใบแจ้งหนี้ {{ $invoice->invoice_number }}</h5>
    <span class="badge bg-{{ $invoice->status_badge }}">{{ $invoice->status_label }}</span>
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-sm btn-outline-danger" target="_blank">
            <i class="bi bi-file-pdf me-1"></i>PDF
        </a>
        @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
        <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" onsubmit="return confirm('ยืนยันการลบ?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">รายละเอียดใบแจ้งหนี้</div>
            <div class="card-body">
                <div class="row mb-3 small text-muted">
                    <div class="col-6">
                        <strong>ห้อง:</strong> {{ $invoice->rental->room->room_number }}<br>
                        <strong>ผู้เช่า:</strong> {{ $invoice->rental->tenant->name }}<br>
                        <strong>โทร:</strong> {{ $invoice->rental->tenant->phone ?? '-' }}
                    </div>
                    <div class="col-6 text-end">
                        <strong>เลขที่:</strong> {{ $invoice->invoice_number }}<br>
                        <strong>ประจำเดือน:</strong> {{ $invoice->month_name }} {{ $invoice->year }}<br>
                        <strong>กำหนดชำระ:</strong> {{ $invoice->due_date->format('d/m/Y') }}
                    </div>
                </div>
                <table class="table table-sm">
                    <tbody>
                        <tr>
                            <td>ค่าห้องพัก</td>
                            <td class="text-end fw-semibold">{{ number_format($invoice->room_charge, 2) }}</td>
                        </tr>
                        <tr>
                            <td>
                                ค่าไฟฟ้า {{ number_format($invoice->electricity_units, 2) }} หน่วย
                                <small class="text-muted">× {{ number_format($invoice->electricity_rate, 4) }} บ./หน่วย</small>
                            </td>
                            <td class="text-end">{{ number_format($invoice->electricity_charge, 2) }}</td>
                        </tr>
                        <tr>
                            <td>
                                ค่าน้ำประปา {{ number_format($invoice->water_units, 2) }} หน่วย
                                <small class="text-muted">× {{ number_format($invoice->water_rate, 4) }} บ./หน่วย</small>
                            </td>
                            <td class="text-end">{{ number_format($invoice->water_charge, 2) }}</td>
                        </tr>
                        @if($invoice->other_charge > 0)
                        <tr>
                            <td>ค่าใช้จ่ายอื่นๆ</td>
                            <td class="text-end">{{ number_format($invoice->other_charge, 2) }}</td>
                        </tr>
                        @endif
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-active fw-bold">
                            <td>รวมทั้งสิ้น</td>
                            <td class="text-end fs-5">{{ number_format($invoice->total_amount, 2) }} บาท</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($invoice->payments->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header text-success"><i class="bi bi-cash me-2"></i>ประวัติการชำระเงิน</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>วันที่</th><th>วิธีชำระ</th><th class="text-end">จำนวน</th><th>อ้างอิง</th></tr></thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td>{{ $payment->payment_method_label }}</td>
                            <td class="text-end">{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->reference_number ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-5">
        @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
        <div class="card border-success">
            <div class="card-header bg-success text-white"><i class="bi bi-cash me-2"></i>บันทึกการชำระเงิน</div>
            <div class="card-body">
                <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">วันที่ชำระ <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">วิธีชำระ</label>
                        <select name="payment_method" class="form-select">
                            <option value="cash">เงินสด</option>
                            <option value="transfer">โอนเงิน</option>
                            <option value="other">อื่นๆ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">เลขอ้างอิง</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="เลขที่โอน (ถ้ามี)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">หมายเหตุ</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-success">{{ number_format($invoice->total_amount, 2) }} บาท</span>
                        <button class="btn btn-success"><i class="bi bi-check-lg me-1"></i>ยืนยันชำระ</button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="card border-success">
            <div class="card-body text-center py-4">
                <i class="bi bi-check-circle-fill text-success fs-1"></i>
                <div class="mt-2 fw-semibold text-success">ชำระเงินแล้ว</div>
                @if($invoice->paid_at)
                <small class="text-muted">{{ $invoice->paid_at->format('d/m/Y H:i') }}</small>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
