@extends('layouts.app')
@section('title', 'เพิ่มห้องพัก')

@section('content')
<div class="d-flex align-items-center mb-3 gap-2">
    <a href="{{ route('rooms.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">เพิ่มห้องพักใหม่</h5>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('rooms.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">เลขห้อง <span class="text-danger">*</span></label>
                <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror"
                    value="{{ old('room_number') }}" placeholder="เช่น 101, A201">
                @error('room_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">ชั้น <span class="text-danger">*</span></label>
                <input type="number" name="floor" class="form-control @error('floor') is-invalid @enderror"
                    value="{{ old('floor', 1) }}" min="1">
                @error('floor')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">ประเภทห้อง <span class="text-danger">*</span></label>
                <select name="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror">
                    <option value="">-- เลือกประเภทห้อง --</option>
                    @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}" {{ old('room_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ number_format($type->base_price, 0) }} บ./เดือน)
                        </option>
                    @endforeach
                </select>
                @error('room_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">รายละเอียดเพิ่มเติม</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึก</button>
                <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
@endsection
