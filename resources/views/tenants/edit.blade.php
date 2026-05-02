@extends('layouts.app')
@section('title', 'แก้ไขข้อมูลผู้เช่า')

@section('content')
<div class="d-flex align-items-center mb-3 gap-2">
    <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">แก้ไขข้อมูล: {{ $tenant->name }}</h5>
</div>
<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ route('tenants.update', $tenant) }}">
            @csrf @method('PUT')
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $tenant->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">เลขบัตรประชาชน</label>
                    <input type="text" name="id_card" class="form-control" value="{{ old('id_card', $tenant->id_card) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">โทรศัพท์</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $tenant->phone) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">อีเมล</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $tenant->email) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">ที่อยู่</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address', $tenant->address) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">ชื่อผู้ติดต่อฉุกเฉิน</label>
                    <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $tenant->emergency_contact_name) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">โทรฉุกเฉิน</label>
                    <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $tenant->emergency_contact_phone) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">หมายเหตุ</label>
                    <textarea name="note" class="form-control" rows="2">{{ old('note', $tenant->note) }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึก</button>
                <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-outline-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
@endsection
