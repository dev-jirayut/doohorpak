@extends('layouts.app')
@section('title', 'เพิ่มที่พัก')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('admin.properties.index') }}" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-bold">เพิ่มที่พัก</h5>
</div>

<div class="card" style="max-width:560px">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('admin.properties.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">ชื่อที่พัก <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}" placeholder="เช่น หอพักสุขใจ, โรงแรมริมน้ำ">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">ประเภท <span class="text-danger">*</span></label>
                <select name="type" class="form-select @error('type') is-invalid @enderror">
                    <option value="dormitory" {{ old('type') === 'dormitory' ? 'selected' : '' }}>หอพัก (เก็บค่าเช่ารายเดือน)</option>
                    <option value="hotel" {{ old('type') === 'hotel' ? 'selected' : '' }}>โรงแรม (เก็บค่าพักรายวัน)</option>
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">ที่อยู่</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
            </div>
            <div class="mb-4">
                <label class="form-label">เบอร์โทรศัพท์</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-dark">บันทึก</button>
                <a href="{{ route('admin.properties.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
@endsection
