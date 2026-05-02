@extends('layouts.app')
@section('title', 'LINE Rich Menu')

@section('content')
@php
    $actions = old() ?: ($setting?->rich_menu_actions ?? $defaultActions);
@endphp

<div class="page-header">
    <div>
        <h1><i class="bi bi-grid-3x3-gap me-2" style="color:#00A884"></i>LINE Rich Menu</h1>
        <div class="text-muted small">สร้างเมนูหลักใน LINE OA สำหรับผู้เช่าของหอพัก</div>
    </div>
    <a href="{{ route('settings.line') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-chat-dots me-1"></i>ตั้งค่า LINE
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="rich-menu-layout">
    <div class="card detail-card">
        <div class="card-header">
            <h5 class="title"><span class="icon"><i class="bi bi-image"></i></span>รูปและข้อมูล Rich Menu</h5>
        </div>
        <form method="POST" action="{{ route('settings.line.rich-menu.store') }}" enctype="multipart/form-data" class="card-body">
            @csrf

            <div class="form-group">
                <label class="form-label fw-semibold">รูป Rich Menu <span class="text-danger">*</span></label>
                <input type="file" name="image" id="richMenuImage" class="form-control @error('image') is-invalid @enderror" accept="image/png,image/jpeg" required>
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="text-muted small mt-1">แนะนำขนาด 2500 x 1686 px, JPG/PNG, ไม่เกิน 1MB</div>
            </div>

            <div class="row g-3">
                <div class="col-md-7">
                    <div class="form-group">
                        <label class="form-label fw-semibold">ชื่อ Rich Menu</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ $actions['name'] ?? $defaultActions['name'] }}" maxlength="300" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label class="form-label fw-semibold">ข้อความแถบเมนู</label>
                        <input type="text" name="chat_bar_text" class="form-control @error('chat_bar_text') is-invalid @enderror"
                            value="{{ $actions['chat_bar_text'] ?? $defaultActions['chat_bar_text'] }}" maxlength="14" required>
                        @error('chat_bar_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="rich-menu-action-grid">
                @foreach([
                    ['invoice', 'ตรวจสอบใบแจ้งหนี้', 'bi-receipt'],
                    ['parcel', 'นัดรับพัสดุ', 'bi-box-seam'],
                    ['maintenance', 'แจ้งซ่อม', 'bi-tools'],
                    ['contract', 'ดูสัญญาหอ', 'bi-file-earmark-check'],
                    ['contact', 'ติดต่อหอพัก', 'bi-chat-left-dots'],
                ] as [$key, $label, $icon])
                <div class="rich-menu-action-field">
                    <label class="form-label fw-semibold"><i class="bi {{ $icon }} me-1"></i>{{ $label }}</label>
                    <input type="text" name="{{ $key }}" class="form-control @error($key) is-invalid @enderror"
                        value="{{ $actions[$key] ?? $defaultActions[$key] }}" maxlength="300" required>
                    @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @endforeach
            </div>

            <div class="d-flex gap-2 flex-wrap mt-3">
                <button class="btn btn-primary">
                    <i class="bi bi-magic me-1"></i>สร้าง Rich Menu
                </button>
                @if($setting?->rich_menu_id)
                <span class="badge badge-success align-self-center">
                    ใช้งานอยู่: {{ $setting->rich_menu_id }}
                </span>
                @endif
            </div>
        </form>
    </div>

    <div class="card detail-card">
        <div class="card-header">
            <h5 class="title"><span class="icon"><i class="bi bi-phone"></i></span>Preview</h5>
        </div>
        <div class="card-body">
            <div class="rich-menu-preview">
                <img id="richMenuPreviewImage"
                    src="{{ $setting?->rich_menu_image_path ? Storage::url($setting->rich_menu_image_path) : '' }}"
                    alt="LINE Rich Menu preview"
                    @unless($setting?->rich_menu_image_path) style="display:none" @endunless>
                <div id="richMenuPlaceholder" class="rich-menu-placeholder" @if($setting?->rich_menu_image_path) style="display:none" @endif>
                    <i class="bi bi-image"></i>
                    <span>เลือกรูปเพื่อดูตัวอย่าง</span>
                </div>
                <div class="rich-menu-hotspot hotspot-invoice">ใบแจ้งหนี้</div>
                <div class="rich-menu-hotspot hotspot-parcel">พัสดุ</div>
                <div class="rich-menu-hotspot hotspot-maintenance">แจ้งซ่อม</div>
                <div class="rich-menu-hotspot hotspot-contract">สัญญา</div>
                <div class="rich-menu-hotspot hotspot-contact">ติดต่อ</div>
            </div>

            <div class="rich-menu-current">
                <div>
                    <span>Rich Menu ID</span>
                    <strong>{{ $setting?->rich_menu_id ?? '-' }}</strong>
                </div>
                <div>
                    <span>สร้างล่าสุด</span>
                    <strong>{{ $setting?->rich_menu_created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('richMenuImage')?.addEventListener('change', function (event) {
    const file = event.target.files?.[0];
    if (!file) return;

    const url = URL.createObjectURL(file);
    const preview = document.getElementById('richMenuPreviewImage');
    const placeholder = document.getElementById('richMenuPlaceholder');

    preview.src = url;
    preview.style.display = 'block';
    placeholder.style.display = 'none';
});
</script>
@endsection
