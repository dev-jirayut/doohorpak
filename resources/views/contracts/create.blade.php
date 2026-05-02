@extends('layouts.app')
@section('title', 'สร้างสัญญาใหม่')

@section('content')
<div class="page-header">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('contracts.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1>สร้างสัญญาใหม่</h1>
            <div class="text-muted small">เลือกสัญญาเช่าที่ยังไม่มีเอกสารสัญญา แล้วกำหนดช่วงวันที่</div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('contracts.store') }}" enctype="multipart/form-data" data-max-total-upload-mb="30">
    @csrf
    <div class="row g-3">
        <div class="col-md-7">
            <div class="card detail-card">
                <div class="card-header">
                    <h5 class="title"><span class="icon"><i class="bi bi-file-earmark-plus"></i></span>ข้อมูลสัญญา</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">สัญญาเช่า <span class="text-danger">*</span></label>
                        <select name="rental_id" class="form-select @error('rental_id') is-invalid @enderror" required>
                            <option value="">-- เลือกสัญญาเช่า --</option>
                            @foreach($rentals as $rental)
                                <option value="{{ $rental->id }}" {{ old('rental_id') == $rental->id ? 'selected' : '' }}>
                                    ห้อง {{ $rental->room?->room_number }} · {{ $rental->tenant?->name }}
                                    ({{ number_format($rental->monthly_rent, 0) }} บ./เดือน)
                                </option>
                            @endforeach
                        </select>
                        @error('rental_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($rentals->isEmpty())
                            <div class="text-muted small mt-2">ยังไม่มีสัญญาเช่าที่พร้อมสร้างเอกสารใหม่</div>
                        @endif
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">วันที่เริ่ม <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date', date('Y-m-d')) }}" required>
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                value="{{ old('end_date', now()->addYear()->toDateString()) }}" required>
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">ไฟล์สัญญา PDF</label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept="application/pdf">
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="text-muted small mt-1">รองรับไฟล์ PDF ขนาดไม่เกิน 10MB</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">สำเนาบัตรประชาชน <span class="text-danger">*</span></label>
                            <input type="file" name="tenant_id_card_copy"
                                class="form-control @error('tenant_id_card_copy') is-invalid @enderror"
                                accept="application/pdf,image/jpeg,image/png" required>
                            @error('tenant_id_card_copy')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="text-muted small mt-1">รองรับ PDF, JPG, PNG</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">รูปสัญญากระดาษต้นฉบับ <span class="text-danger">*</span></label>
                            <input type="file" name="paper_contract_images[]"
                                class="form-control @error('paper_contract_images') is-invalid @enderror @error('paper_contract_images.*') is-invalid @enderror"
                                accept="image/jpeg,image/png" multiple required>
                            @error('paper_contract_images')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @error('paper_contract_images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="text-muted small mt-1">แนบได้หลายรูป เช่น หน้า 1, หน้า 2, หน้าลายเซ็น ไฟล์ละไม่เกิน 10MB</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">เงื่อนไขสัญญา</label>
                        <textarea name="terms" class="form-control @error('terms') is-invalid @enderror" rows="5"
                            placeholder="เช่น ชำระค่าเช่าภายในวันที่ 5 ของทุกเดือน...">{{ old('terms') }}</textarea>
                        @error('terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" @disabled($rentals->isEmpty())><i class="bi bi-check-lg me-1"></i>สร้างสัญญา</button>
                        <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card detail-card">
                <div class="card-header">
                    <h5 class="title"><span class="icon"><i class="bi bi-info-circle"></i></span>คำแนะนำ</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-lightbulb me-1"></i>
                        ระบบจะแสดงเฉพาะสัญญาเช่าที่ยังไม่มีสัญญา active เพื่อป้องกันการสร้างซ้ำ
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
