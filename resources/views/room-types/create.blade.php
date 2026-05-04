@extends('layouts.app')
@section('title', 'เพิ่มประเภทห้อง')

@section('content')
<div class="d-flex align-items-center mb-3 gap-2">
    <a href="{{ route('room-types.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">เพิ่มประเภทห้องใหม่</h5>
</div>
<div class="card" style="max-width:500px">
    <div class="card-body">
        <form method="POST" action="{{ route('room-types.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">ชื่อประเภทห้อง <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" maxlength="100" placeholder="เช่น ห้องเดี่ยว, ห้องคู่">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">ค่าห้องพื้นฐาน (บาท/เดือน) <span class="text-danger">*</span></label>
                <input type="text" name="base_price" class="form-control @error('base_price') is-invalid @enderror"
                    value="{{ old('base_price') !== null ? preg_replace('/\D/', '', old('base_price')) : 0 }}"
                    inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/\D/g, '')">
                @error('base_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">รายละเอียด</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึก</button>
                <a href="{{ route('room-types.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
@endsection
