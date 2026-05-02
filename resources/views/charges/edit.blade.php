@extends('layouts.app')
@section('title', 'แก้ไขค่าใช้จ่าย')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('charges.index') }}" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-bold">แก้ไขค่าใช้จ่าย: {{ $charge->name }}</h5>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- ข้อมูลหลัก --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">ข้อมูลรายการ</div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('charges.update', $charge) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">ชื่อรายการ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $charge->name) }}">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0"
                            class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount', $charge->amount) }}">
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ประเภท</label>
                        <select name="type" class="form-select">
                            <option value="monthly" {{ $charge->type === 'monthly' ? 'selected' : '' }}>รายเดือน</option>
                            <option value="one_time" {{ $charge->type === 'one_time' ? 'selected' : '' }}>รายครั้ง</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $charge->description) }}</textarea>
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                {{ $charge->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">เปิดใช้งาน</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark">บันทึก</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ห้องที่มอบหมาย --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>ห้องที่เก็บค่านี้</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>ห้อง</th>
                            <th>เริ่ม</th>
                            <th>สิ้นสุด</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignedRooms as $room)
                        <tr>
                            <td>{{ $room->room_number }}</td>
                            <td class="small">{{ \Carbon\Carbon::parse($room->pivot->active_from)->format('d/m/Y') }}</td>
                            <td class="small text-muted">{{ $room->pivot->active_to ? \Carbon\Carbon::parse($room->pivot->active_to)->format('d/m/Y') : 'ไม่มีกำหนด' }}</td>
                            <td>
                                <form method="POST" action="{{ route('charges.detach-room', [$charge, $room]) }}" class="d-inline"
                                    onsubmit="return confirm('ลบห้อง {{ $room->room_number }} ออก?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">ยังไม่มีห้องที่มอบหมาย</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- เพิ่มห้องใหม่ --}}
            <div class="card-footer bg-transparent">
                <form method="POST" action="{{ route('charges.assign-room', $charge) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-4">
                        <label class="form-label small mb-1">ห้อง</label>
                        <select name="room_id" class="form-select form-select-sm">
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->room_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3">
                        <label class="form-label small mb-1">เริ่มต้น</label>
                        <input type="date" name="active_from" class="form-control form-control-sm"
                            value="{{ date('Y-m-01') }}">
                    </div>
                    <div class="col-3">
                        <label class="form-label small mb-1">สิ้นสุด</label>
                        <input type="date" name="active_to" class="form-control form-control-sm">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-dark btn-sm w-100">เพิ่ม</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
