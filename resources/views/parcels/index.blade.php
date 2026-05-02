@extends('layouts.app')
@section('title', 'พัสดุ / จดหมาย')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="bi bi-box-seam me-2" style="color:#00A884"></i>พัสดุ / จดหมาย</h1>
        <div style="font-size:.82rem;color:#888;margin-top:.1rem">
            รอรับ <strong style="color:#f5a623">{{ $waitingCount }}</strong> รายการ ·
            แจ้งแล้ว <strong style="color:#3498db">{{ $notifiedCount }}</strong> รายการ
        </div>
    </div>
    <a href="{{ route('parcels.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> บันทึกพัสดุใหม่
    </a>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:1rem;padding:1rem 1.25rem">
    <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
        <div>
            <label class="form-label">สถานะ</label>
            <select name="status" class="form-select" style="width:140px">
                <option value="">ทั้งหมด</option>
                <option value="waiting"   {{ request('status')=='waiting'   ?'selected':'' }}>รอรับ</option>
                <option value="notified"  {{ request('status')=='notified'  ?'selected':'' }}>แจ้งแล้ว</option>
                <option value="collected" {{ request('status')=='collected' ?'selected':'' }}>รับแล้ว</option>
                <option value="returned"  {{ request('status')=='returned'  ?'selected':'' }}>ส่งคืน</option>
            </select>
        </div>
        <div>
            <label class="form-label">ประเภท</label>
            <select name="type" class="form-select" style="width:130px">
                <option value="">ทั้งหมด</option>
                <option value="parcel"   {{ request('type')=='parcel'   ?'selected':'' }}>📦 พัสดุ</option>
                <option value="letter"   {{ request('type')=='letter'   ?'selected':'' }}>✉️ จดหมาย</option>
                <option value="document" {{ request('type')=='document' ?'selected':'' }}>📄 เอกสาร</option>
                <option value="food"     {{ request('type')=='food'     ?'selected':'' }}>🍱 อาหาร</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">ค้นหา</button>
        <a href="{{ route('parcels.index') }}" class="btn btn-secondary">รีเซ็ต</a>
    </form>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <div class="table-wrapper" style="border:none;border-radius:0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ห้อง / ผู้เช่า</th>
                    <th>ประเภท</th>
                    <th>ขนส่ง / เลขพัสดุ</th>
                    <th>สถานะ</th>
                    <th>รับเมื่อ</th>
                    <th>แจ้งแล้ว</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($parcels as $parcel)
                <tr>
                    <td style="font-size:.78rem;color:#888">{{ $parcel->parcel_number }}</td>
                    <td>
                        <div style="font-weight:700">{{ $parcel->room?->room_number }}</div>
                        <div style="font-size:.78rem;color:#888">{{ $parcel->tenant?->name ?? '—' }}</div>
                    </td>
                    <td>
                        <span style="font-size:1.1rem">{{ $parcel->type_icon }}</span>
                        <span style="font-size:.82rem;margin-left:.25rem">{{ $parcel->type_label }}</span>
                    </td>
                    <td>
                        @if($parcel->carrier)
                        <div style="font-size:.82rem;font-weight:600">{{ $parcel->carrier }}</div>
                        @endif
                        @if($parcel->tracking_number)
                        <div style="font-size:.75rem;color:#888;font-family:monospace">{{ $parcel->tracking_number }}</div>
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $parcel->status_color }}">{{ $parcel->status_label }}</span></td>
                    <td style="font-size:.78rem;color:#888">{{ $parcel->received_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($parcel->notified_at)
                        <span style="color:#00A884;font-size:.82rem"><i class="bi bi-check-circle-fill me-1"></i>{{ $parcel->notified_at->format('H:i') }}</span>
                        @else
                        <span style="color:#888;font-size:.8rem">—</span>
                        @endif
                    </td>
                    <td style="display:flex;gap:.4rem">
                        @if(in_array($parcel->status, ['waiting', 'notified']))
                        <form method="POST" action="{{ route('parcels.resend', $parcel) }}" style="display:inline">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm" title="แจ้งเตือนซ้ำ"><i class="bi bi-bell"></i></button>
                        </form>
                        <form method="POST" action="{{ route('parcels.collect', $parcel) }}" style="display:inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-primary btn-sm" title="บันทึกรับแล้ว">
                                <i class="bi bi-check2"></i> รับแล้ว
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:3rem;color:#888">
                        <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:.5rem;color:#ccc"></i>
                        ยังไม่มีพัสดุ
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($parcels->hasPages())
    <div style="padding:1rem 1.25rem">{{ $parcels->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
