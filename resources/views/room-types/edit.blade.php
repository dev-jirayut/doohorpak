@extends('layouts.app')
@section('title', 'แก้ไขประเภทห้อง')

@section('content')
<div class="d-flex align-items-center mb-3 gap-2">
    <a href="{{ route('room-types.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">แก้ไขประเภทห้อง: {{ $roomType->name }}</h5>
</div>
<div class="card" style="max-width:500px">
    <div class="card-body">
        <form method="POST" action="{{ route('room-types.update', $roomType) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">ชื่อประเภทห้อง <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $roomType->name) }}" maxlength="100">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">ค่าห้องพื้นฐาน (บาท/เดือน) <span class="text-danger">*</span></label>
                <input type="number" name="base_price" class="form-control @error('base_price') is-invalid @enderror" value="{{ old('base_price', $roomType->base_price) }}" min="0" step="0.01">
                @error('base_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">รายละเอียด</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $roomType->description) }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึก</button>
                <a href="{{ route('room-types.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
@endsection
