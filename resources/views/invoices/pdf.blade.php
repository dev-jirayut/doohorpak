<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<style>
    @font-face {
        font-family: 'Sarabun';
        src: url('{{ storage_path('fonts/Sarabun-Regular.ttf') }}') format('truetype');
        font-weight: normal;
    }
    @font-face {
        font-family: 'Sarabun';
        src: url('{{ storage_path('fonts/Sarabun-Bold.ttf') }}') format('truetype');
        font-weight: bold;
    }
    @page { margin: 15mm; }
    body { font-family: 'Sarabun', sans-serif; font-size: 14px; color: #222; margin: 0; }
    .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
    .header h2 { margin: 0; font-size: 20px; }
    .header p { margin: 2px 0; font-size: 13px; color: #555; }
    .invoice-meta { display: table; width: 100%; margin-bottom: 15px; }
    .meta-left, .meta-right { display: table-cell; width: 50%; vertical-align: top; }
    .meta-right { text-align: right; }
    .meta-label { color: #666; font-size: 12px; }
    .invoice-title { background: #1a2035; color: #fff; text-align: center; padding: 8px; font-size: 16px; font-weight: bold; margin-bottom: 5px; }
    .invoice-month { text-align: center; color: #444; margin-bottom: 15px; font-size: 13px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f3f4f6; padding: 7px 10px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: .03em; border-bottom: 2px solid #ddd; }
    td { padding: 7px 10px; border-bottom: 1px solid #eee; font-size: 13px; }
    .text-right { text-align: right; }
    .total-row { background: #f9fafb; }
    .total-row td { font-size: 15px; font-weight: bold; border-top: 2px solid #333; }
    .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px; }
    .sign-table { width: 100%; }
    .sign-cell { width: 50%; text-align: center; padding-top: 40px; }
    .sign-line { border-top: 1px solid #555; width: 60%; margin: 0 auto 5px; }
    .due-box { background: #fff8e1; border: 2px solid #f59e0b; padding: 10px; text-align: center; margin: 15px 0; border-radius: 4px; }
    .due-box strong { font-size: 15px; color: #b45309; }
    .paid-stamp { position: fixed; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-15deg); border: 5px solid #10b981; color: #10b981; font-size: 40px; font-weight: bold; padding: 10px 20px; opacity: .3; white-space: nowrap; }
</style>
</head>
<body>

@if($invoice->status === 'paid')
<div class="paid-stamp">ชำระแล้ว</div>
@endif

<div class="header">
    <h2>หอพัก</h2>
    <p>ที่อยู่: 123 ถ.ตัวอย่าง ต.ตัวอย่าง อ.ตัวอย่าง จ.ตัวอย่าง 12345</p>
    <p>โทร: 099-999-9999</p>
</div>

<div class="invoice-title">ใบแจ้งหนี้ค่าเช่าห้องพัก</div>
<div class="invoice-month">ประจำเดือน {{ $invoice->month_name }} {{ $invoice->year }}</div>

<div class="invoice-meta">
    <div class="meta-left">
        <div class="meta-label">ชื่อผู้เช่า</div>
        <strong>{{ $invoice->rental->tenant->name }}</strong><br>
        <div class="meta-label">ห้องพัก</div>
        <strong>{{ $invoice->rental->room->room_number }}</strong>
        ({{ $invoice->rental->room->roomType->name }})
        @if($invoice->rental->tenant->phone)
        <br><div class="meta-label">โทรศัพท์: {{ $invoice->rental->tenant->phone }}</div>
        @endif
    </div>
    <div class="meta-right">
        <div class="meta-label">เลขที่ใบแจ้งหนี้</div>
        <strong>{{ $invoice->invoice_number }}</strong><br>
        <div class="meta-label">วันที่ออก</div>
        {{ now()->format('d/m/Y') }}<br>
        <div class="meta-label">กำหนดชำระ</div>
        <strong style="color:#dc2626">{{ $invoice->due_date->format('d/m/Y') }}</strong>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>รายการ</th>
            <th class="text-right">หน่วย</th>
            <th class="text-right">ราคา/หน่วย</th>
            <th class="text-right">จำนวนเงิน (บาท)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>ค่าห้องพัก ประจำเดือน {{ $invoice->month_name }} {{ $invoice->year }}</td>
            <td class="text-right">1 เดือน</td>
            <td class="text-right">{{ number_format($invoice->room_charge, 2) }}</td>
            <td class="text-right">{{ number_format($invoice->room_charge, 2) }}</td>
        </tr>
        <tr>
            <td>
                ค่าไฟฟ้า (หน่วยเดิม: {{ number_format($invoice->electricity_units + 0, 0) }})
            </td>
            <td class="text-right">{{ number_format($invoice->electricity_units, 2) }} หน่วย</td>
            <td class="text-right">{{ number_format($invoice->electricity_rate, 4) }}</td>
            <td class="text-right">{{ number_format($invoice->electricity_charge, 2) }}</td>
        </tr>
        <tr>
            <td>ค่าน้ำประปา</td>
            <td class="text-right">{{ number_format($invoice->water_units, 2) }} หน่วย</td>
            <td class="text-right">{{ number_format($invoice->water_rate, 4) }}</td>
            <td class="text-right">{{ number_format($invoice->water_charge, 2) }}</td>
        </tr>
        @if($invoice->other_charge > 0)
        <tr>
            <td>ค่าใช้จ่ายอื่นๆ</td>
            <td class="text-right">-</td>
            <td class="text-right">-</td>
            <td class="text-right">{{ number_format($invoice->other_charge, 2) }}</td>
        </tr>
        @endif
        @foreach($invoice->items as $item)
        <tr>
            <td>{{ $item->description }}</td>
            <td class="text-right">{{ $item->quantity }}</td>
            <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
            <td class="text-right">{{ number_format($item->amount, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="3" class="text-right">รวมยอดชำระทั้งสิ้น</td>
            <td class="text-right" style="font-size:16px;">{{ number_format($invoice->total_amount, 2) }} บาท</td>
        </tr>
    </tfoot>
</table>

<div class="due-box">
    <strong>กรุณาชำระภายในวันที่ {{ $invoice->due_date->format('d/m/Y') }}</strong>
</div>

<div class="footer">
    <table class="sign-table">
        <tr>
            <td class="sign-cell">
                <div class="sign-line"></div>
                <div>ผู้เช่า</div>
                <small>({{ $invoice->rental->tenant->name }})</small>
            </td>
            <td class="sign-cell">
                <div class="sign-line"></div>
                <div>ผู้รับเงิน / เจ้าของหอพัก</div>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
