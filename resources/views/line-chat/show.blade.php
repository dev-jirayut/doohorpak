@extends('layouts.app')
@section('title', $conversation->display_label)

@php use Illuminate\Support\Facades\Storage; @endphp

@push('styles')
<style>
.chat-layout { display:flex; gap:1.5rem; align-items:flex-start; }
.chat-thread { flex:1; min-width:0; display:flex; flex-direction:column; gap:0; }
.chat-sidebar { width:280px; flex-shrink:0; }
.chat-messages { background:#f8fffe; border-radius:12px; padding:1.25rem; min-height:300px; max-height:60vh; overflow-y:auto; display:flex; flex-direction:column; gap:.75rem; }
.msg { display:flex; align-items:flex-end; gap:.5rem; max-width:80%; }
.msg.inbound { align-self:flex-start; }
.msg.outbound { align-self:flex-end; flex-direction:row-reverse; }
.msg-bubble { padding:.6rem .9rem; border-radius:16px; font-size:.88rem; line-height:1.5; }
.msg.inbound  .msg-bubble { background:#fff; border:1px solid #e8e8e8; color:#002C2C; border-bottom-left-radius:4px; }
.msg.outbound .msg-bubble { background:#00A884; color:#fff; border-bottom-right-radius:4px; }
.msg-meta { font-size:.68rem; color:#aaa; margin-top:.2rem; }
.msg.outbound .msg-meta { text-align:right; }
.chat-input { display:flex; gap:.5rem; padding:1rem; background:#fff; border:1px solid #e5e5e5; border-radius:12px; margin-top:1rem; }
.chat-input textarea { flex:1; border:none; outline:none; resize:none; font-family:inherit; font-size:.9rem; color:#002C2C; }
.chat-input textarea::placeholder { color:#bbb; }
</style>
@endpush

@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem">
    <a href="{{ route('line-chat.index') }}" style="color:#888;text-decoration:none"><i class="bi bi-arrow-left"></i></a>
    @if($conversation->picture_url)
    <img src="{{ $conversation->picture_url }}" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover">
    @else
    <div style="width:40px;height:40px;border-radius:50%;background:#e8f5f0;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;color:#00A884">
        {{ mb_substr($conversation->display_label, 0, 1) }}
    </div>
    @endif
    <div>
        <div style="font-weight:700;color:#002C2C">{{ $conversation->display_label }}</div>
        <div style="font-size:.75rem;color:#888">LINE: {{ $conversation->line_user_id }}</div>
        @if($conversation->tenant?->line_user_id === $conversation->line_user_id)
        <div style="font-size:.72rem;color:#00A884;font-weight:700"><i class="bi bi-check-circle"></i> ผูกกับผู้เช่าแล้ว</div>
        @else
        <div style="font-size:.72rem;color:#f59e0b;font-weight:700"><i class="bi bi-exclamation-circle"></i> ยังไม่ผูกกับผู้เช่า</div>
        @endif
    </div>
</div>

<div class="chat-layout">
    {{-- ── Thread ── --}}
    <div class="chat-thread">
        <div class="chat-messages" id="chatMessages">
            @forelse($messages as $msg)
            <div class="msg {{ $msg->direction }}" id="msg-{{ $msg->id }}">
                <div>
                    <div class="msg-bubble">
                        @if($msg->type === 'text')
                            {{ $msg->content }}
                        @elseif($msg->type === 'image')
                            @php
                                $imageUrl = $msg->metadata['public_url'] ?? (
                                    !empty($msg->metadata['stored_path'] ?? null) ? Storage::disk('public')->url($msg->metadata['stored_path']) : null
                                );
                            @endphp
                            @if($imageUrl)
                                <a href="{{ $imageUrl }}" target="_blank" rel="noopener">
                                    <img src="{{ $imageUrl }}" alt="LINE image" style="display:block;max-width:260px;max-height:260px;border-radius:10px;object-fit:cover">
                                </a>
                            @else
                                <i class="bi bi-image"></i> <em style="opacity:.7">รูปภาพยังไม่ได้ถูกดาวน์โหลด</em>
                                @if(!empty($msg->metadata['download_error'] ?? null))
                                    <div style="font-size:.72rem;opacity:.65;margin-top:.25rem">{{ $msg->metadata['download_error'] }}</div>
                                @endif
                            @endif
                        @elseif($msg->type === 'sticker')
                            <i class="bi bi-emoji-smile" style="font-size:1.5rem"></i>
                        @else
                            <i class="bi bi-paperclip"></i> {{ $msg->type }}
                        @endif
                    </div>
                    <div class="msg-meta">
                        @if($msg->isOutbound()){{ $msg->sentBy?->name ?? 'ทีมงาน' }} · @endif
                        {{ $msg->created_at->format('d/m H:i') }}
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;color:#ccc;padding:2rem">ยังไม่มีข้อความ</div>
            @endforelse
        </div>

        {{-- Reply input --}}
        <form method="POST" action="{{ route('line-chat.reply', $conversation) }}" id="replyForm">
            @csrf
            <div class="chat-input">
                <textarea name="message" id="replyMsg" rows="2" placeholder="พิมพ์ข้อความ…" required
                    onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();document.getElementById('replyForm').submit()}"></textarea>
                <button type="submit" class="btn btn-primary" style="align-self:flex-end">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- ── Sidebar ── --}}
    <div class="chat-sidebar">
        {{-- Label / Link tenant --}}
        <div class="card" style="margin-bottom:1rem">
            <div class="card-header"><h6 style="margin:0">ตั้งชื่อแชท / ผูก LINE กับผู้เช่า</h6></div>
            <div style="padding:1rem">
                <form method="POST" action="{{ route('line-chat.label', $conversation) }}">
                    @csrf @method('PATCH')
                    <div class="form-group" style="margin-bottom:.75rem">
                        <label class="form-label">ชื่อแชท (ปรับเอง)</label>
                        <input type="text" name="chat_name" class="form-control" value="{{ $conversation->chat_name }}" placeholder="เว้นว่าง = ใช้ชื่อผู้เช่า">
                    </div>
                    <div class="form-group" style="margin-bottom:.75rem">
                        <label class="form-label">ผูก LINE นี้กับผู้เช่า</label>
                        <select name="tenant_id" class="form-control form-select">
                            <option value="">-- ไม่เชื่อม --</option>
                            @foreach($allTenants as $t)
                            <option value="{{ $t->id }}" @selected($conversation->tenant_id === $t->id)>
                                {{ $t->name }}
                                @if($t->activeRental?->room) - ห้อง {{ $t->activeRental->room->room_number }}@endif
                            </option>
                            @endforeach
                        </select>
                        <div style="font-size:.75rem;color:#888;margin-top:.3rem">เมื่อบันทึก ระบบจะเก็บ LINE userId นี้ไว้ในข้อมูลผู้เช่า เพื่อส่งใบแจ้งหนี้และข้อความแจ้งเตือน</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-block">บันทึก</button>
                </form>
            </div>
        </div>

        {{-- Tenant info --}}
        @if($conversation->tenant)
        @php $rental = $conversation->tenant->activeRental; @endphp
        <div class="card">
            <div class="card-header"><h6 style="margin:0">ข้อมูลผู้เช่า</h6></div>
            <div style="padding:1rem;font-size:.85rem">
                <div style="margin-bottom:.5rem"><strong>{{ $conversation->tenant->name }}</strong></div>
                @if($rental)
                <div style="color:#888;margin-bottom:.25rem"><i class="bi bi-door-open"></i> ห้อง {{ $rental->room?->room_number }}</div>
                <div style="color:#888;margin-bottom:.25rem"><i class="bi bi-cash"></i> ค่าเช่า ฿{{ number_format($rental->monthly_rent) }}/เดือน</div>
                @endif
                @if($conversation->tenant->phone)
                <div style="color:#888"><i class="bi bi-telephone"></i> {{ $conversation->tenant->phone }}</div>
                @endif
                <a href="{{ route('tenants.show', $conversation->tenant) }}" class="btn btn-secondary btn-sm" style="margin-top:.75rem;display:block;text-align:center">
                    ดูโปรไฟล์ผู้เช่า
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Scroll to bottom of chat
const chatEl = document.getElementById('chatMessages');
if (chatEl) chatEl.scrollTop = chatEl.scrollHeight;
</script>
@endpush
