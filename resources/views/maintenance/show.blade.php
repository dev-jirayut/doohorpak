@extends('layouts.app')
@section('title', 'รายละเอียดคำขอซ่อม')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="bi bi-tools me-2" style="color:#00A884"></i>{{ $maintenance->title }}</h1>
        <div style="font-size:.82rem;color:#888">{{ $maintenance->request_number }} · แจ้งเมื่อ {{ $maintenance->created_at->format('d/m/Y H:i') }}</div>
    </div>
    <a href="{{ route('maintenance.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> กลับ</a>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:1rem">

    {{-- Detail Card --}}
    <div class="card">
        <div class="card-header">
            <h5>รายละเอียด</h5>
            <span class="badge badge-{{ $maintenance->status_color }}">{{ $maintenance->status_label }}</span>
        </div>
        <div style="padding:1.5rem">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem">
                <div>
                    <div style="font-size:.75rem;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.04em">ห้อง</div>
                    <div style="font-size:1rem;font-weight:700;color:#002C2C;margin-top:.25rem">{{ $maintenance->room?->room_number }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.04em">ผู้แจ้ง</div>
                    <div style="font-size:.9rem;color:#002C2C;margin-top:.25rem">{{ $maintenance->tenant?->name ?? auth()->user()->name }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.04em">หมวดหมู่</div>
                    <div style="margin-top:.25rem"><span class="badge badge-secondary">{{ $maintenance->category_label }}</span></div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.04em">ความสำคัญ</div>
                    <div style="margin-top:.25rem">
                        @php $pc = match($maintenance->priority) { 'urgent'=>'danger','high'=>'warning','normal'=>'info', default=>'secondary' }; @endphp
                        <span class="badge badge-{{ $pc }}">{{ $maintenance->priority_label }}</span>
                    </div>
                </div>
            </div>

            <div style="font-size:.75rem;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem">คำอธิบาย</div>
            <div style="background:#f7f5ee;border-radius:8px;padding:1rem;font-size:.9rem;line-height:1.7;color:#002C2C;white-space:pre-wrap">{{ $maintenance->description }}</div>

            @if($maintenance->image_path)
            <div style="margin-top:1rem">
                <div style="font-size:.75rem;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem">รูปภาพ</div>
                <img src="{{ Storage::url($maintenance->image_path) }}" alt="maintenance image"
                    style="max-width:100%;border-radius:8px;border:1px solid #eee">
            </div>
            @endif

            @if($maintenance->video_path)
            <div style="margin-top:1rem">
                <div style="font-size:.75rem;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem">วิดีโอ</div>
                <video src="{{ Storage::url($maintenance->video_path) }}" controls playsinline
                    style="max-width:100%;border-radius:8px;border:1px solid #eee;background:#000"></video>
            </div>
            @endif

            @if($maintenance->technician_note)
            <div style="margin-top:1.25rem">
                <div style="font-size:.75rem;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.5rem">หมายเหตุช่าง</div>
                <div style="background:rgba(0,168,132,.06);border-left:3px solid #00A884;border-radius:0 8px 8px 0;padding:.85rem 1rem;font-size:.875rem;color:#002C2C">
                    {{ $maintenance->technician_note }}
                </div>
            </div>
            @endif

            @if($maintenance->resolved_at)
            <div style="margin-top:1rem;font-size:.82rem;color:#00A884">
                <i class="bi bi-check-circle-fill me-1"></i>แก้ไขเสร็จเมื่อ {{ $maintenance->resolved_at->format('d/m/Y H:i') }}
            </div>
            @endif
        </div>
    </div>

    {{-- Update Status --}}
    <div style="display:flex;flex-direction:column;gap:1rem">
        <div class="card">
            <div class="card-header"><h6>อัปเดตสถานะ</h6></div>
            <form method="POST" action="{{ route('maintenance.update', $maintenance) }}" style="padding:1.25rem">
                @csrf @method('PATCH')

                <div class="form-group">
                    <label class="form-label">สถานะ</label>
                    <select name="status" class="form-select" required>
                        <option value="pending"     {{ $maintenance->status=='pending'     ?'selected':'' }}>รอดำเนินการ</option>
                        <option value="in_progress" {{ $maintenance->status=='in_progress' ?'selected':'' }}>กำลังดำเนินการ</option>
                        <option value="done"        {{ $maintenance->status=='done'        ?'selected':'' }}>เสร็จสิ้น</option>
                        <option value="cancelled"   {{ $maintenance->status=='cancelled'   ?'selected':'' }}>ยกเลิก</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">มอบหมายให้</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">— ไม่ระบุ —</option>
                        @foreach($staff as $u)
                        <option value="{{ $u->id }}" {{ $maintenance->assigned_to==$u->id?'selected':'' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">หมายเหตุช่าง</label>
                    <textarea name="technician_note" rows="3" class="form-control"
                        placeholder="บันทึกการซ่อม...">{{ $maintenance->technician_note }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-save me-1"></i>บันทึก</button>
            </form>
        </div>

        {{-- Timeline --}}
        <div class="card">
            <div class="card-header"><h6>ประวัติ</h6></div>
            <div style="padding:1rem">
                <div style="display:flex;flex-direction:column;gap:.75rem">
                    <div style="display:flex;gap:.75rem;align-items:flex-start">
                        <div style="width:8px;height:8px;border-radius:50%;background:#00A884;margin-top:.35rem;flex-shrink:0"></div>
                        <div>
                            <div style="font-size:.82rem;font-weight:600">แจ้งซ่อม</div>
                            <div style="font-size:.72rem;color:#888">{{ $maintenance->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                    @if($maintenance->resolved_at)
                    <div style="display:flex;gap:.75rem;align-items:flex-start">
                        <div style="width:8px;height:8px;border-radius:50%;background:#00A884;margin-top:.35rem;flex-shrink:0"></div>
                        <div>
                            <div style="font-size:.82rem;font-weight:600">ซ่อมเสร็จ</div>
                            <div style="font-size:.72rem;color:#888">{{ $maintenance->resolved_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
