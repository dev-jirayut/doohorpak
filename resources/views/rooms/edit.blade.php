@extends('layouts.app')
@section('title', 'แก้ไขห้องพัก')

@section('content')
<div class="d-flex align-items-center mb-3 gap-2">
    <a href="{{ route('rooms.show', $room) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">แก้ไขห้อง {{ $room->room_number }}</h5>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="{{ route('rooms.update', $room) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold">เลขห้อง <span class="text-danger">*</span></label>
                <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror"
                    value="{{ old('room_number', $room->room_number) }}">
                @error('room_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">ชั้น <span class="text-danger">*</span></label>
                <input type="number" name="floor" class="form-control @error('floor') is-invalid @enderror"
                    value="{{ old('floor', $room->floor) }}" min="1">
                @error('floor')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">ประเภทห้อง <span class="text-danger">*</span></label>
                <select name="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror">
                    @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}" {{ old('room_type_id', $room->room_type_id) == $type->id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ number_format($type->base_price, 0) }} บ./เดือน)
                        </option>
                    @endforeach
                </select>
                @error('room_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">สถานะ</label>
                <select name="status" class="form-select">
                    <option value="available" {{ $room->status == 'available' ? 'selected' : '' }}>ว่าง</option>
                    <option value="occupied"  {{ $room->status == 'occupied'  ? 'selected' : '' }}>มีผู้เช่า</option>
                    <option value="reserved"  {{ $room->status == 'reserved'  ? 'selected' : '' }}>จองแล้ว</option>
                    <option value="maintenance" {{ $room->status == 'maintenance' ? 'selected' : '' }}>ซ่อมบำรุง</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">รายละเอียดเพิ่มเติม</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $room->description) }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึก</button>
                <a href="{{ route('rooms.show', $room) }}" class="btn btn-outline-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
@endsection
