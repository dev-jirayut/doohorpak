@extends('layouts.app')
@section('title', 'ตั้งค่า LINE Messaging API')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-chat-dots me-2" style="color:#00A884"></i>ตั้งค่า LINE Messaging API</h1>
</div>

<div style="max-width:640px">
    <div class="card">
        <div class="card-header"><h5>การแจ้งเตือนผ่าน LINE</h5></div>
        <form method="POST" action="{{ route('settings.line.store') }}" style="padding:1.5rem">
            @csrf

            @if(! $property && auth()->user()->isSuperAdmin())
            <div class="form-group">
                <label class="form-label">หอพัก <span class="text-danger">*</span></label>
                <select name="property_id" class="form-select @error('property_id') is-invalid @enderror">
                    <option value="">-- เลือกหอพัก --</option>
                    @foreach($properties as $item)
                        <option value="{{ $item->id }}" {{ old('property_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
                @error('property_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @endif

            <div class="form-group">
                <label class="form-label">LINE OA Channel Access Token</label>
                <input type="password" name="oa_channel_access_token" class="form-control @error('oa_channel_access_token') is-invalid @enderror"
                    value="{{ old('oa_channel_access_token') }}"
                    placeholder="{{ $setting?->oa_channel_access_token ? 'ตั้งค่าแล้ว - เว้นว่างไว้ถ้าไม่เปลี่ยน' : 'สำหรับส่ง Flex Message ให้ผู้เช่า' }}"
                    autocomplete="new-password">
                @error('oa_channel_access_token')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">LINE OA Channel Secret</label>
                <input type="password" name="oa_channel_secret" class="form-control @error('oa_channel_secret') is-invalid @enderror"
                    value="{{ old('oa_channel_secret') }}"
                    placeholder="{{ $setting?->oa_channel_secret ? 'ตั้งค่าแล้ว - เว้นว่างไว้ถ้าไม่เปลี่ยน' : 'สำหรับตรวจสอบ Webhook signature' }}"
                    autocomplete="new-password">
                @error('oa_channel_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">LINE User IDs สำหรับแจ้งเจ้าของ/แอดมิน</label>
                <textarea name="admin_line_user_ids" rows="4" class="form-control"
                    placeholder="ใส่ LINE userId ทีละบรรทัด เช่น Uxxxxxxxxxxxxxxxx">{{ old('admin_line_user_ids', implode("\n", $setting?->admin_line_user_ids ?? [])) }}</textarea>
                <div style="font-size:.78rem;color:#888;margin-top:.25rem">
                    Messaging API ส่งข้อความหาเจ้าของ/แอดมินผ่าน userId เหล่านี้ แทน LINE Notify ที่ยุติบริการแล้ว
                </div>
            </div>

            <input type="hidden" name="notify_token" value="{{ old('notify_token') }}">

            <div style="background:#f7f5ee;border-radius:10px;padding:1rem;margin-bottom:1.25rem">
                <div style="font-size:.85rem;font-weight:700;color:#002C2C;margin-bottom:.75rem">เหตุการณ์ที่แจ้งเตือน</div>
                <div style="display:flex;flex-direction:column;gap:.6rem">
                    @foreach([
                        ['notify_on_invoice',     'ออกใบแจ้งหนี้ประจำเดือน'],
                        ['notify_on_overdue',     'ใบแจ้งหนี้เกินกำหนด (แจ้งทุกวัน)'],
                        ['notify_on_maintenance', 'แจ้งซ่อมใหม่'],
                        ['notify_on_new_tenant',  'ผู้เช่าใหม่เข้าอยู่'],
                    ] as [$key, $label])
                    <label style="display:flex;align-items:center;gap:.65rem;cursor:pointer;font-size:.875rem">
                        <input type="checkbox" name="{{ $key }}" value="1"
                            {{ old($key, $setting?->$key ?? true) ? 'checked' : '' }}
                            style="width:16px;height:16px;accent-color:#00A884">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">เวลาแจ้งเตือน (สำหรับรายวัน)</label>
                <input type="time" name="reminder_time" class="form-control @error('reminder_time') is-invalid @enderror"
                    value="{{ old('reminder_time', $setting?->reminder_time ?? '09:00') }}" style="max-width:150px">
                @error('reminder_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึก</button>
        </form>
    </div>
</div>
@endsection
