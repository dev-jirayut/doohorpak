@extends('layouts.app')
@section('title', 'เพิ่มค่าใช้จ่ายเพิ่มเติม')

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('charges.index') }}" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-bold">เพิ่มค่าใช้จ่ายเพิ่มเติม</h5>
</div>

<form method="POST" action="{{ route('charges.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">ข้อมูลรายการ</div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label">ชื่อรายการ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="เช่น ค่า Internet, ค่าเช่าเฟอร์นิเจอร์">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ราคา (บาท) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0"
                            class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount') }}">
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ประเภท <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                            <option value="monthly" {{ old('type') === 'monthly' ? 'selected' : '' }}>รายเดือน (เก็บทุกเดือน)</option>
                            <option value="one_time" {{ old('type') === 'one_time' ? 'selected' : '' }}>รายครั้ง (เก็บครั้งเดียว)</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">เริ่มใช้งาน <span class="text-danger">*</span></label>
                            <input type="date" name="active_from"
                                class="form-control @error('active_from') is-invalid @enderror"
                                value="{{ old('active_from', date('Y-m-01')) }}">
                            @error('active_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6" id="active_to_wrap">
                            <label class="form-label">สิ้นสุด <small class="text-muted">(รายเดือน: เว้นว่าง=ไม่มีกำหนด)</small></label>
                            <input type="date" name="active_to"
                                class="form-control @error('active_to') is-invalid @enderror"
                                value="{{ old('active_to') }}">
                            @error('active_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">เลือกห้องที่เก็บค่านี้</div>
                <div class="card-body p-3" style="max-height:400px;overflow-y:auto">
                    @php $currentFloor = null; @endphp
                    @foreach($rooms as $room)
                        @if($room->floor !== $currentFloor)
                            @if($currentFloor !== null) <hr class="my-2"> @endif
                            <div class="text-muted small fw-bold mb-1 mt-2">ชั้น {{ $room->floor }}</div>
                            @php $currentFloor = $room->floor; @endphp
                        @endif
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="room_ids[]"
                                value="{{ $room->id }}" id="room_{{ $room->id }}"
                                {{ in_array($room->id, old('room_ids', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="room_{{ $room->id }}">
                                ห้อง {{ $room->room_number }}
                                <span class="text-muted small">({{ $room->roomType->name }})</span>
                                @if($room->status !== 'occupied')
                                    <span class="badge bg-secondary" style="font-size:.65rem">{{ $room->status_label }}</span>
                                @endif
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer bg-transparent py-2 px-3">
                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none"
                        onclick="document.querySelectorAll('[name=\'room_ids[]\']').forEach(c=>c.checked=true)">เลือกทั้งหมด</button>
                    &nbsp;|&nbsp;
                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none"
                        onclick="document.querySelectorAll('[name=\'room_ids[]\']').forEach(c=>c.checked=false)">ล้าง</button>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-dark">บันทึก</button>
        <a href="{{ route('charges.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
    </div>
</form>
@endsection
