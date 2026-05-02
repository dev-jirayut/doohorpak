@extends('layouts.app')
@section('title', 'เพิ่มผู้เช่า')

@section('content')
<div class="d-flex align-items-center mb-3 gap-2">
    <a href="{{ route('tenants.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">เพิ่มผู้เช่าใหม่</h5>
</div>
<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ route('tenants.store') }}">
            @csrf
            <h6 class="text-muted mb-3">ข้อมูลส่วนตัว</h6>
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">เลขบัตรประชาชน</label>
                    <input type="text" name="id_card" class="form-control" value="{{ old('id_card') }}" maxlength="20">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">โทรศัพท์</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">อีเมล</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">ที่อยู่</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                </div>
            </div>
            <h6 class="text-muted mb-3">ผู้ติดต่อฉุกเฉิน</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">ชื่อผู้ติดต่อ</label>
                    <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">โทรศัพท์</label>
                    <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone') }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">หมายเหตุ</label>
                    <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึก</button>
                <a href="{{ route('tenants.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
@endsection
