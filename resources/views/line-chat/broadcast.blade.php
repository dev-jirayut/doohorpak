@extends('layouts.app')
@section('title', 'ส่งข้อความถึงทั้งหอ')

@section('content')
<div class="page-header">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('line-chat.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1><i class="bi bi-megaphone me-2" style="color:#00A884"></i>ส่งข้อความถึงทั้งหอ</h1>
            <div class="text-muted small">ส่งข้อความ LINE ให้ผู้เช่าทุกคนใน {{ $property->name }}</div>
        </div>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-3">
    <div class="col-md-7">
        <div class="card detail-card">
            <div class="card-header">
                <h5 class="title"><span class="icon"><i class="bi bi-send"></i></span>ข้อความประกาศ</h5>
            </div>
            <form method="POST" action="{{ route('line-chat.broadcast.send') }}" class="card-body">
                @csrf

                <div class="form-group">
                    <label class="form-label fw-semibold">ข้อความ <span class="text-danger">*</span></label>
                    <textarea name="message" rows="8" class="form-control @error('message') is-invalid @enderror"
                        maxlength="2000" required
                        placeholder="เช่น แจ้งปิดน้ำวันที่ 10 เวลา 09:00-12:00 ขออภัยในความไม่สะดวก">{{ old('message') }}</textarea>
                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="text-muted small mt-1">ส่งเป็นข้อความ LINE แบบ text สูงสุด 2,000 ตัวอักษร</div>
                </div>

                <label class="broadcast-confirm">
                    <input type="checkbox" name="confirm_send" value="1" required>
                    <span>ยืนยันว่าต้องการส่งข้อความนี้ถึงผู้เช่าที่มี LINE ทั้งหมด {{ $tenantsWithLine->count() }} คน</span>
                </label>
                @error('confirm_send')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-primary" @disabled(!$setting?->oa_channel_access_token || $tenantsWithLine->isEmpty())>
                        <i class="bi bi-send-check me-1"></i>ส่งข้อความ
                    </button>
                    <a href="{{ route('line-chat.index') }}" class="btn btn-outline-secondary">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card detail-card">
            <div class="card-header">
                <h5 class="title"><span class="icon"><i class="bi bi-people"></i></span>ผู้รับข้อความ</h5>
            </div>
            <div class="card-body">
                <div class="broadcast-summary">
                    <div>
                        <span>ผู้รับที่มี LINE</span>
                        <strong>{{ $tenantsWithLine->count() }} คน</strong>
                    </div>
                    <div>
                        <span>สถานะ LINE OA</span>
                        <strong>{{ $setting?->oa_channel_access_token ? 'พร้อมส่ง' : 'ยังไม่ตั้งค่า' }}</strong>
                    </div>
                </div>

                @unless($setting?->oa_channel_access_token)
                    <div class="alert alert-warning mt-3">
                        ยังไม่ได้ตั้งค่า Channel Access Token กรุณาตั้งค่า LINE ก่อนส่งข้อความ
                    </div>
                @endunless

                <div class="broadcast-recipient-list">
                    @forelse($tenantsWithLine as $tenant)
                    <div class="broadcast-recipient">
                        <div class="avatar">{{ mb_substr($tenant->name, 0, 1) }}</div>
                        <div>
                            <strong>{{ $tenant->name }}</strong>
                            <span>ห้อง {{ $tenant->activeRental?->room?->room_number ?? '-' }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-muted small">ยังไม่มีผู้เช่าที่ผูก LINE userId</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
