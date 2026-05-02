@extends('layouts.app')
@section('title', 'แดชบอร์ด')

@section('content')

{{-- ─── Stat Cards ──────────────────────────────────────────────────── --}}
<div class="dashboard-grid">
    <div class="card-stat">
        <div class="icon icon-jade"><i class="bi bi-door-open"></i></div>
        <div>
            <div class="value">{{ $totalRooms }}</div>
            <div class="label">ห้องทั้งหมด</div>
            <div style="font-size:.72rem;color:#888;margin-top:.2rem">เข้าพัก {{ $occupancyRate }}%</div>
        </div>
    </div>
    <div class="card-stat">
        <div class="icon icon-midnight"><i class="bi bi-people-fill"></i></div>
        <div>
            <div class="value">{{ $occupiedRooms }}</div>
            <div class="label">ห้องมีผู้เช่า</div>
            <div style="font-size:.72rem;color:#888;margin-top:.2rem">ว่าง {{ $availableRooms }} ห้อง</div>
        </div>
    </div>
    <div class="card-stat">
        <div class="icon icon-warning"><i class="bi bi-receipt"></i></div>
        <div>
            <div class="value">{{ $pendingInvoices }}</div>
            <div class="label">รอชำระ</div>
            @if($overdueInvoices > 0)
            <div style="font-size:.72rem;color:#e74c3c;margin-top:.2rem;font-weight:600">⚠ เกินกำหนด {{ $overdueInvoices }}</div>
            @else
            <div style="font-size:.72rem;color:#888;margin-top:.2rem">ทุกใบอยู่ในกำหนด</div>
            @endif
        </div>
    </div>
    <div class="card-stat">
        <div class="icon icon-jade"><i class="bi bi-cash-coin"></i></div>
        <div>
            <div class="value" style="font-size:1.3rem">฿{{ number_format($monthlyIncome, 0) }}</div>
            <div class="label">รายรับเดือนนี้</div>
            <div style="font-size:.72rem;color:#888;margin-top:.2rem">{{ now()->locale('th')->translatedFormat('M Y') }}</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:1rem" class="chart-grid">

    {{-- ─── Pending Invoices table ──────────────────────────────────── --}}
    <div class="card dashboard-panel">
        <div class="card-header">
            <h6><i class="bi bi-receipt me-2" style="color:#00A884"></i>ใบแจ้งหนี้รอชำระ</h6>
            <a href="{{ route('invoices.index', ['status'=>'pending']) }}" class="btn btn-ghost btn-sm">ดูทั้งหมด →</a>
        </div>
        <div class="table-wrapper" style="border:none;border-radius:0">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>เลขที่</th>
                        <th>ห้อง / ผู้เช่า</th>
                        <th>เดือน</th>
                        <th style="text-align:right">ยอด</th>
                        <th>ครบกำหนด</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentInvoices as $inv)
                    <tr>
                        <td style="font-size:.78rem;color:#888">{{ $inv->invoice_number }}</td>
                        <td>
                            @if($inv->rental)
                            <div style="font-weight:700;font-size:.875rem">{{ $inv->rental->room->room_number }}</div>
                            <div style="font-size:.78rem;color:#888">{{ $inv->rental->tenant->name }}</div>
                            @endif
                        </td>
                        <td style="font-size:.85rem">{{ $inv->month }}/{{ $inv->year }}</td>
                        <td style="text-align:right;font-weight:700;color:#002C2C">฿{{ number_format($inv->total_amount, 0) }}</td>
                        <td>
                            @if($inv->due_date && $inv->due_date->isPast())
                            <span class="badge badge-danger"><i class="bi bi-exclamation-circle"></i> {{ $inv->due_date->format('d/m/Y') }}</span>
                            @elseif($inv->due_date)
                            <span style="font-size:.82rem;color:#666">{{ $inv->due_date->format('d/m/Y') }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('invoices.show', $inv) }}" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:2.5rem;color:#888">
                            <i class="bi bi-check-circle" style="font-size:2rem;color:#00A884;display:block;margin-bottom:.5rem"></i>
                            ไม่มีใบแจ้งหนี้รอชำระ
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─── Right column ─────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:1rem">

        {{-- Occupancy Donut --}}
        <div class="card" style="text-align:center">
            <div class="card-header"><h6>อัตราการเข้าพัก</h6></div>
            <div style="padding:1.25rem 1rem">
                <div style="position:relative;display:inline-block;width:110px;height:110px">
                    <svg viewBox="0 0 36 36" style="width:110px;height:110px;transform:rotate(-90deg)">
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#F7F5EE" stroke-width="3.5"/>
                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#00A884" stroke-width="3.5"
                            stroke-dasharray="{{ $totalRooms > 0 ? round($occupiedRooms / $totalRooms * 100) : 0 }}, 100"
                            stroke-linecap="round"/>
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
                        <div style="font-size:1.4rem;font-weight:800;color:#002C2C">{{ $occupancyRate }}%</div>
                    </div>
                </div>
                <div style="display:flex;justify-content:center;gap:1.5rem;margin-top:.75rem">
                    <div>
                        <div style="font-size:1rem;font-weight:700;color:#00A884">{{ $occupiedRooms }}</div>
                        <div style="font-size:.72rem;color:#888">มีผู้เช่า</div>
                    </div>
                    <div style="width:1px;background:#eee"></div>
                    <div>
                        <div style="font-size:1rem;font-weight:700;color:#002C2C">{{ $availableRooms }}</div>
                        <div style="font-size:.72rem;color:#888">ว่าง</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card">
            <div class="card-header"><h6>ทางลัด</h6></div>
            <div style="padding:.75rem;display:grid;gap:.5rem">
                <a href="{{ route('meter-readings.index') }}" class="btn btn-ghost" style="justify-content:flex-start;font-size:.85rem">
                    <i class="bi bi-lightning-charge" style="color:#00A884"></i> บันทึกมิเตอร์
                </a>
                <a href="{{ route('invoices.generate-form') }}" class="btn btn-ghost" style="justify-content:flex-start;font-size:.85rem">
                    <i class="bi bi-receipt" style="color:#f5a623"></i> ออกใบแจ้งหนี้
                </a>
                <a href="{{ route('rentals.create') }}" class="btn btn-ghost" style="justify-content:flex-start;font-size:.85rem">
                    <i class="bi bi-person-plus" style="color:#3498db"></i> เปิดการเช่าใหม่
                </a>
                <a href="{{ route('maintenance.create') }}" class="btn btn-ghost" style="justify-content:flex-start;font-size:.85rem">
                    <i class="bi bi-tools" style="color:#e74c3c"></i> แจ้งซ่อม
                </a>
            </div>
        </div>

        {{-- Pending Maintenance --}}
        @if($pendingMaintenance && $pendingMaintenance->count() > 0)
        <div class="card dashboard-panel">
            <div class="card-header">
                <h6><i class="bi bi-tools me-2" style="color:#e74c3c"></i>แจ้งซ่อมรอดำเนินการ</h6>
                <a href="{{ route('maintenance.index') }}" class="btn btn-ghost btn-sm">ดูทั้งหมด</a>
            </div>
            <div style="padding:.5rem">
                @foreach($pendingMaintenance as $maint)
                <a href="{{ route('maintenance.show', $maint) }}" style="display:flex;gap:.75rem;padding:.65rem .75rem;border-radius:8px;text-decoration:none;transition:background .1s" onmouseover="this.style.background='#f7f5ee'" onmouseout="this.style.background=''">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(231,76,60,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-tools" style="color:#e74c3c;font-size:.85rem"></i>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:.82rem;font-weight:600;color:#002C2C;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $maint->title }}</div>
                        <div style="font-size:.72rem;color:#888">ห้อง {{ $maint->room?->room_number }} · <span class="badge badge-{{ $maint->status_color }}">{{ $maint->status_label }}</span></div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

@endsection
