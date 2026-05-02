@extends('layouts.app')
@section('title', 'แจ้งซ่อมใหม่')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-plus-lg me-2" style="color:#00A884"></i>แจ้งซ่อมใหม่</h1>
    <a href="{{ route('maintenance.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> กลับ</a>
</div>

<div style="max-width:640px">
    <div class="card">
        <div class="card-header"><h5>รายละเอียดการแจ้งซ่อม</h5></div>
        <form method="POST" action="{{ route('maintenance.store') }}" enctype="multipart/form-data" style="padding:1.5rem">
            @csrf
            <div class="form-group">
                <label class="form-label">ห้อง <span style="color:#e74c3c">*</span></label>
                <select name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                    <option value="">— เลือกห้อง —</option>
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ old('room_id')==$room->id?'selected':'' }}>
                        {{ $room->room_number }}{{ $room->floor ? ' (ชั้น '.$room->floor.')' : '' }}
                    </option>
                    @endforeach
                </select>
                @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">หมวดหมู่ <span style="color:#e74c3c">*</span></label>
                    <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                        <option value="general"     {{ old('category','general')=='general'     ?'selected':'' }}>ทั่วไป</option>
                        <option value="electrical"  {{ old('category')=='electrical'  ?'selected':'' }}>ไฟฟ้า</option>
                        <option value="plumbing"    {{ old('category')=='plumbing'    ?'selected':'' }}>ประปา</option>
                        <option value="furniture"   {{ old('category')=='furniture'   ?'selected':'' }}>เฟอร์นิเจอร์</option>
                        <option value="other"       {{ old('category')=='other'       ?'selected':'' }}>อื่นๆ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">ความสำคัญ <span style="color:#e74c3c">*</span></label>
                    <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                        <option value="low"    {{ old('priority')=='low'    ?'selected':'' }}>⚪ ต่ำ</option>
                        <option value="normal" {{ old('priority','normal')=='normal' ?'selected':'' }}>🟡 ปกติ</option>
                        <option value="high"   {{ old('priority')=='high'   ?'selected':'' }}>🟠 สูง</option>
                        <option value="urgent" {{ old('priority')=='urgent' ?'selected':'' }}>🔴 เร่งด่วน</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">หัวข้อ <span style="color:#e74c3c">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                    value="{{ old('title') }}" placeholder="เช่น ก๊อกน้ำรั่ว, ไฟในห้องน้ำดับ" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">รายละเอียด <span style="color:#e74c3c">*</span></label>
                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror"
                    placeholder="อธิบายปัญหาให้ชัดเจน..." required>{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">รูปภาพ (ไม่บังคับ)</label>
                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                <div style="font-size:.78rem;color:#888;margin-top:.25rem">รองรับ JPG, PNG, WEBP ขนาดไม่เกิน 5MB</div>
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">วิดีโอ (ไม่บังคับ)</label>
                <input type="file" name="video" class="form-control @error('video') is-invalid @enderror" accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska">
                <div style="font-size:.78rem;color:#888;margin-top:.25rem">รองรับ MP4, MOV, AVI, MKV ขนาดไม่เกิน 50MB</div>
                @error('video')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:.5rem">
                <a href="{{ route('maintenance.index') }}" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>บันทึกคำขอ</button>
            </div>
        </form>
    </div>
</div>
@endsection
