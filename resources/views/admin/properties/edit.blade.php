@extends('layouts.app')
@section('title', 'แก้ไขที่พัก')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.properties.index') }}" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-bold">แก้ไขที่พัก: {{ $property->name }}</h5>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">ข้อมูลที่พัก</div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.properties.update', $property) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">ชื่อที่พัก <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $property->name) }}">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ประเภท</label>
                        <select name="type" class="form-select">
                            <option value="dormitory" {{ $property->type === 'dormitory' ? 'selected' : '' }}>หอพัก (รายเดือน)</option>
                            <option value="hotel" {{ $property->type === 'hotel' ? 'selected' : '' }}>โรงแรม (รายวัน)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ที่อยู่</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $property->address) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เบอร์โทรศัพท์</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $property->phone) }}">
                    </div>
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                {{ $property->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">เปิดใช้งาน</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark">บันทึก</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">ผู้ใช้งานที่มีสิทธิ์จัดการ</div>
            <div class="card-body p-3">
                <form method="POST" action="{{ route('admin.properties.update', $property) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="name" value="{{ $property->name }}">
                    <input type="hidden" name="type" value="{{ $property->type }}">
                    <input type="hidden" name="is_active" value="{{ $property->is_active ? '1' : '0' }}">
                    @foreach($allUsers as $user)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="user_ids[]"
                            value="{{ $user->id }}" id="user_{{ $user->id }}"
                            {{ in_array($user->id, $assignedIds) ? 'checked' : '' }}>
                        <label class="form-check-label" for="user_{{ $user->id }}">
                            {{ $user->name }}
                            <small class="text-muted">({{ $user->role === 'admin' ? 'แอดมิน' : 'เจ้าหน้าที่' }})</small>
                        </label>
                    </div>
                    @endforeach
                    <div class="mt-3">
                        <button type="submit" class="btn btn-sm btn-dark">บันทึกรายชื่อ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
