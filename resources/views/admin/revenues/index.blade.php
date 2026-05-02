@extends('layouts.app')
@section('title', 'รายได้ Platform')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-graph-up-arrow me-2" style="color:#00A884"></i>รายได้ Platform</h1>
</div>

{{-- Summary cards --}}
<div class="dashboard-grid" style="margin-bottom:1.5rem">
    <div class="card-stat">
        <div class="icon icon-jade"><i class="bi bi-cash-coin"></i></div>
        <div>
            <div class="value" style="font-size:1.3rem">฿{{ number_format($totalFee, 2) }}</div>
            <div class="label">รายได้รวม Platform</div>
        </div>
    </div>
    <div class="card-stat">
        <div class="icon icon-warning"><i class="bi bi-hourglass-split"></i></div>
        <div>
            <div class="value" style="font-size:1.3rem">฿{{ number_format($pendingPayout, 2) }}</div>
            <div class="label">ยอดรอโอนให้เจ้าของ</div>
        </div>
    </div>
    <div class="card-stat">
        <div class="icon icon-danger"><i class="bi bi-receipt-cutoff"></i></div>
        <div>
            <div class="value" style="font-size:1.3rem">฿{{ number_format($ownerReceivable, 2) }}</div>
            <div class="label">รอเก็บค่าระบบจากหอพัก</div>
        </div>
    </div>
    <div class="card-stat">
        <div class="icon icon-midnight"><i class="bi bi-building"></i></div>
        <div>
            <div class="value">{{ $properties->count() }}</div>
            <div class="label">หอพักทั้งหมด</div>
            <div style="font-size:.72rem;color:#888">{{ $properties->sum('rooms_count') }} ห้อง</div>
        </div>
    </div>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <div class="table-wrapper" style="border:none;border-radius:0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>หอพัก</th>
                    <th>ประเภท</th>
                    <th>รอบบิล</th>
                    <th style="text-align:right">ยอดรวม</th>
                    <th style="text-align:right">ค่า Platform</th>
                    <th style="text-align:right">ยอดรอโอน</th>
                    <th>สถานะ</th>
                    <th>วันที่</th>
                </tr>
            </thead>
            <tbody>
                @forelse($revenues as $rev)
                <tr>
                    <td style="font-weight:700">{{ $rev->property?->name }}</td>
                    <td><span class="badge badge-secondary">{{ $rev->type_label }}</span></td>
                    <td>
                        @if($rev->billing_month && $rev->billing_year)
                            {{ str_pad($rev->billing_month, 2, '0', STR_PAD_LEFT) }}/{{ $rev->billing_year }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align:right">฿{{ number_format($rev->gross_amount, 2) }}</td>
                    <td style="text-align:right;color:#00A884;font-weight:700">฿{{ number_format($rev->fee_amount, 2) }}</td>
                    <td style="text-align:right">฿{{ number_format($rev->net_amount, 2) }}</td>
                    <td>
                        <span class="badge {{ $rev->status_badge }}">
                            {{ $rev->status_label }}
                        </span>
                    </td>
                    <td style="font-size:.78rem;color:#888">{{ $rev->created_at->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:3rem;color:#888">ยังไม่มีรายการ</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($revenues->hasPages())
    <div style="padding:1rem 1.25rem">{{ $revenues->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
