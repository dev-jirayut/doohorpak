@extends('layouts.app')
@section('title', 'ประเภทห้อง')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="bi bi-tags me-2" style="color:#00A884"></i>ประเภทห้อง</h1>
        <div class="text-muted small">กำหนดชื่อประเภท ห้องพัก และราคาพื้นฐานต่อเดือน</div>
    </div>
    <a href="{{ route('room-types.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>เพิ่มประเภท
    </a>
</div>

@if($roomTypes->isEmpty())
<div class="card">
    <div class="card-body text-center text-muted py-4">ยังไม่มีประเภทห้อง</div>
</div>
@else
<div class="room-type-grid">
    @foreach($roomTypes as $type)
    <div class="card room-type-card">
        <div class="room-type-top">
            <div class="room-type-icon"><i class="bi bi-tag"></i></div>
            <div>
                <h3>{{ $type->name }}</h3>
                <div class="text-muted small">{{ $type->description ?? 'ไม่มีรายละเอียดเพิ่มเติม' }}</div>
            </div>
        </div>

        <div class="room-type-stats">
            <div>
                <span>ค่าห้อง/เดือน</span>
                <strong>{{ number_format($type->base_price, 0) }} บาท</strong>
            </div>
            <div>
                <span>จำนวนห้อง</span>
                <strong>{{ $type->rooms_count }} ห้อง</strong>
            </div>
        </div>

        <div class="room-type-actions">
            <a href="{{ route('room-types.edit', $type) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>แก้ไข
            </a>
            @if(auth()->user()->isSuperAdmin())
                <form method="POST" action="{{ route('room-types.destroy', $type) }}" onsubmit="return confirm('ยืนยันการลบ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" @disabled($type->rooms_count > 0) title="{{ $type->rooms_count > 0 ? 'มีห้องใช้งานอยู่ ไม่สามารถลบได้' : 'ลบ' }}">
                        <i class="bi bi-trash me-1"></i>ลบ
                    </button>
                </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

@if($roomTypes->hasPages())
<div class="card-footer" style="margin-top:1rem">{{ $roomTypes->links() }}</div>
@endif
@endif
@endsection
