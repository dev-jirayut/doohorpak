@extends('layouts.app')
@section('title', 'บันทึกพัสดุ / จดหมาย')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-plus-lg me-2" style="color:#00A884"></i>บันทึกพัสดุ / จดหมายใหม่</h1>
    <a href="{{ route('parcels.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> กลับ</a>
</div>

<div style="max-width:600px">
    <div class="card">
        <div class="card-header"><h5>ข้อมูลพัสดุ</h5></div>
        <form method="POST" action="{{ route('parcels.store') }}" enctype="multipart/form-data" style="padding:1.5rem">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">ห้อง <span style="color:#e74c3c">*</span></label>
                    <select name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                        <option value="">— เลือกห้อง —</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id')==$room->id?'selected':'' }}>
                            {{ $room->room_number }} — {{ $room->activeRental?->tenant?->name ?? 'ไม่มีผู้เช่า' }}
                        </option>
                        @endforeach
                    </select>
                    @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">ประเภท <span style="color:#e74c3c">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="parcel"   {{ old('type','parcel')=='parcel'   ?'selected':'' }}>📦 พัสดุ</option>
                        <option value="letter"   {{ old('type')=='letter'   ?'selected':'' }}>✉️ จดหมาย</option>
                        <option value="document" {{ old('type')=='document' ?'selected':'' }}>📄 เอกสาร</option>
                        <option value="food"     {{ old('type')=='food'     ?'selected':'' }}>🍱 อาหาร</option>
                    </select>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">บริษัทขนส่ง</label>
                    <select name="carrier" class="form-select">
                        <option value="">— ไม่ระบุ —</option>
                        @foreach(['EMS/ไปรษณีย์ไทย','Kerry Express','Flash Express','J&T Express','DHL','FedEx','Lazada Logistics','Shopee Express','ลาลามูฟ'] as $carrier)
                        <option {{ old('carrier')==$carrier?'selected':'' }}>{{ $carrier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">เลขพัสดุ / Tracking</label>
                    <input type="text" name="tracking_number" class="form-control"
                        value="{{ old('tracking_number') }}" placeholder="EE123456789TH">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">ผู้ส่ง / ชื่อร้าน</label>
                <input type="text" name="sender" class="form-control"
                    value="{{ old('sender') }}" placeholder="Amazon, Lazada, ชื่อคน...">
            </div>

            <div class="form-group">
                <label class="form-label">รับโดย (เจ้าหน้าที่)</label>
                <input type="text" name="received_by" class="form-control"
                    value="{{ old('received_by', auth()->user()->name) }}">
            </div>

            <div class="form-group">
                <label class="form-label">รูปภาพพัสดุ (ไม่บังคับ)</label>
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                <div style="font-size:.78rem;color:#888;margin-top:.25rem">รองรับ JPG, PNG, WEBP ขนาดไม่เกิน 5MB</div>
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">วิดีโอพัสดุ (ไม่บังคับ)</label>
                <input type="file" name="video" class="form-control @error('video') is-invalid @enderror" accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska">
                <div style="font-size:.78rem;color:#888;margin-top:.25rem">รองรับ MP4, MOV, AVI, MKV ขนาดไม่เกิน 50MB</div>
                @error('video')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">หมายเหตุ</label>
                <textarea name="note" rows="2" class="form-control" placeholder="บันทึกเพิ่มเติม...">{{ old('note') }}</textarea>
            </div>

            <div style="background:rgba(0,168,132,.06);border-radius:8px;padding:.85rem;font-size:.82rem;color:#007A60;margin-bottom:1rem">
                <i class="bi bi-line me-1" style="font-size:1rem"></i>
                ระบบจะแจ้งเตือนผู้เช่าผ่าน LINE โดยอัตโนมัติเมื่อบันทึก
            </div>

            <div style="display:flex;gap:.75rem;justify-content:flex-end">
                <a href="{{ route('parcels.index') }}" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>บันทึกและแจ้ง LINE</button>
            </div>
        </form>
    </div>
</div>
@endsection
