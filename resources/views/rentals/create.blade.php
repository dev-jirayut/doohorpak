@extends('layouts.app')
@section('title', 'เปิดสัญญาเช่าใหม่')

@section('content')
<div class="d-flex align-items-center mb-3 gap-2">
    <a href="{{ route('rentals.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">เปิดสัญญาเช่าใหม่</h5>
</div>
<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="POST" action="{{ route('rentals.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">ห้องพัก <span class="text-danger">*</span></label>
                <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror">
                    <option value="">-- เลือกห้อง --</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}"
                            data-price="{{ $room->roomType->base_price }}"
                            {{ old('room_id', request('room_id')) == $room->id ? 'selected' : '' }}>
                            ห้อง {{ $room->room_number }} ({{ $room->roomType->name }}) - {{ number_format($room->roomType->base_price, 0) }} บ./เดือน
                        </option>
                    @endforeach
                </select>
                @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">ผู้เช่า <span class="text-danger">*</span></label>
                <select name="tenant_id" class="form-select @error('tenant_id') is-invalid @enderror">
                    <option value="">-- เลือกผู้เช่า --</option>
                    @foreach($tenants as $tenant)
                        <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                            {{ $tenant->name }} {{ $tenant->phone ? '('.$tenant->phone.')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('tenant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">ค่าเช่า/เดือน (บาท) <span class="text-danger">*</span></label>
                    <input type="number" name="monthly_rent" id="monthly_rent" class="form-control @error('monthly_rent') is-invalid @enderror"
                        value="{{ old('monthly_rent') }}" min="0" step="0.01">
                    @error('monthly_rent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">ค่ามัดจำ (บาท) <span class="text-danger">*</span></label>
                    <input type="number" name="deposit_amount" class="form-control @error('deposit_amount') is-invalid @enderror"
                        value="{{ old('deposit_amount', 0) }}" min="0" step="0.01">
                    @error('deposit_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">วันที่เข้าพัก <span class="text-danger">*</span></label>
                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                    value="{{ old('start_date', date('Y-m-d')) }}">
                @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">หมายเหตุ</label>
                <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึกสัญญาเช่า</button>
                <a href="{{ route('rentals.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.getElementById('room_id').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const price = opt.dataset.price || '';
    if (price) document.getElementById('monthly_rent').value = price;
});
</script>
@endpush
