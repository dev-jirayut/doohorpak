@extends('layouts.app')
@section('title', 'ระบบแจ้งซ่อม')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-tools me-2" style="color:#00A884"></i>ระบบแจ้งซ่อม</h1>
    <a href="{{ route('maintenance.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> แจ้งซ่อมใหม่
    </a>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:1rem;padding:1rem 1.25rem">
    <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
        <div>
            <label class="form-label">สถานะ</label>
            <select name="status" class="form-select" style="width:150px">
                <option value="">ทั้งหมด</option>
                <option value="pending"     {{ request('status')=='pending'     ?'selected':'' }}>รอดำเนินการ</option>
                <option value="in_progress" {{ request('status')=='in_progress' ?'selected':'' }}>กำลังดำเนินการ</option>
                <option value="done"        {{ request('status')=='done'        ?'selected':'' }}>เสร็จสิ้น</option>
                <option value="cancelled"   {{ request('status')=='cancelled'   ?'selected':'' }}>ยกเลิก</option>
            </select>
        </div>
        <div>
            <label class="form-label">ความสำคัญ</label>
            <select name="priority" class="form-select" style="width:140px">
                <option value="">ทั้งหมด</option>
                <option value="urgent" {{ request('priority')=='urgent' ?'selected':'' }}>🔴 เร่งด่วน</option>
                <option value="high"   {{ request('priority')=='high'   ?'selected':'' }}>🟠 สูง</option>
                <option value="normal" {{ request('priority')=='normal' ?'selected':'' }}>🟡 ปกติ</option>
                <option value="low"    {{ request('priority')=='low'    ?'selected':'' }}>⚪ ต่ำ</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">ค้นหา</button>
        <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">รีเซ็ต</a>
    </form>
</div>

<div class="card" style="padding:0;overflow:hidden">
    <div class="table-wrapper" style="border:none;border-radius:0">
        <table class="data-table">
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ห้อง</th>
                    <th>เรื่อง</th>
                    <th>หมวดหมู่</th>
                    <th>ความสำคัญ</th>
                    <th>สถานะ</th>
                    <th>ผู้รับผิดชอบ</th>
                    <th>วันที่แจ้ง</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td style="font-size:.78rem;color:#888">{{ $req->request_number }}</td>
                    <td style="font-weight:700">{{ $req->room?->room_number }}</td>
                    <td>
                        <div style="font-size:.875rem;font-weight:600;color:#002C2C">{{ $req->title }}</div>
                        @if($req->tenant)
                        <div style="font-size:.75rem;color:#888">{{ $req->tenant->name }}</div>
                        @endif
                    </td>
                    <td><span class="badge badge-secondary">{{ $req->category_label }}</span></td>
                    <td>
                        @php
                        $pc = match($req->priority) { 'urgent'=>'danger','high'=>'warning','normal'=>'info', default=>'secondary' };
                        @endphp
                        <span class="badge badge-{{ $pc }}">{{ $req->priority_label }}</span>
                    </td>
                    <td><span class="badge badge-{{ $req->status_color }}">{{ $req->status_label }}</span></td>
                    <td style="font-size:.82rem;color:#666">{{ $req->assignedTo?->name ?? '—' }}</td>
                    <td style="font-size:.78rem;color:#888">{{ $req->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('maintenance.show', $req) }}" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:3rem;color:#888">
                        <i class="bi bi-tools" style="font-size:2rem;display:block;margin-bottom:.5rem;color:#ccc"></i>
                        ยังไม่มีคำขอซ่อม
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
    <div style="padding:1rem 1.25rem">{{ $requests->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
