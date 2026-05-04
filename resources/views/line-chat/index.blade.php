@extends('layouts.app')
@section('title', 'LINE OA Chat')

@section('content')
<div class="page-header">
    <div>
        <h4 class="page-title">LINE OA Chat</h4>
        <p style="color:#888;margin:0;font-size:.85rem">แชทจาก LINE Official Account ของหอพัก</p>
    </div>
    @if($property?->lineSetting?->oa_channel_access_token)
    <div style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap">
        <a href="{{ route('line-chat.broadcast') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-megaphone me-1"></i>ส่งถึงทั้งหอ
        </a>
        <div style="font-size:.8rem;color:#888">
            <i class="bi bi-link-45deg"></i> Webhook URL:
            <code style="background:#f5f5f5;padding:.15rem .4rem;border-radius:4px;font-size:.75rem">{{ $property->lineSetting?->webhook_url ?? route('webhooks.line', $property->id) }}</code>
        </div>
    </div>
    @elseif(!$property && auth()->user()->isSuperAdmin())
    <span class="badge badge-secondary">เลือกหอพักเพื่อส่งข้อความหรือดู Webhook URL</span>
    @else
    <a href="{{ route('settings.line') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-gear"></i> ตั้งค่า LINE OA ก่อน
    </a>
    @endif
</div>

@if($conversations->isEmpty())
<div class="card" style="text-align:center;padding:3rem 2rem">
    <i class="bi bi-chat-square-text" style="font-size:3rem;color:#ccc;display:block;margin-bottom:1rem"></i>
    <p style="color:#888;margin:0">ยังไม่มีการสนทนา<br>เมื่อผู้เช่าส่งข้อความมาทาง LINE OA จะปรากฏที่นี่</p>
</div>
@else
<div class="card" style="padding:0;overflow:hidden">
    @foreach($conversations as $conv)
    @php $latest = $conv->latestMessage->first(); @endphp
    <a href="{{ route('line-chat.show', $conv) }}" style="display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;border-bottom:1px solid #f0f0f0;text-decoration:none;color:inherit;transition:background .15s;{{ $conv->has_unread ? 'background:#f0fdf9' : '' }}"
       onmouseover="this.style.background='#f8fffe'" onmouseout="this.style.background='{{ $conv->has_unread ? '#f0fdf9' : '' }}'">

        {{-- Avatar --}}
        <div style="flex-shrink:0;position:relative">
            @if($conv->picture_url)
            <img src="{{ $conv->picture_url }}" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover">
            @else
            <div style="width:48px;height:48px;border-radius:50%;background:#e8f5f0;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:700;color:#00A884">
                {{ mb_substr($conv->display_label, 0, 1) }}
            </div>
            @endif
            @if($conv->has_unread)
            <span style="position:absolute;top:0;right:0;width:12px;height:12px;background:#00A884;border-radius:50%;border:2px solid #fff"></span>
            @endif
        </div>

        {{-- Info --}}
        <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem">
                <span style="font-weight:{{ $conv->has_unread ? '700' : '500' }};color:#002C2C;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    {{ $conv->display_label }}
                </span>
                @if($conv->last_message_at)
                <span style="font-size:.72rem;color:#aaa;flex-shrink:0">{{ $conv->last_message_at->diffForHumans() }}</span>
                @endif
            </div>
            @if($latest)
            <div style="font-size:.82rem;color:#888;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:.15rem">
                @if($latest->isOutbound())<span style="color:#00A884">คุณ: </span>@endif
                @if($latest->type === 'text'){{ $latest->content }}
                @elseif($latest->type === 'image')<i class="bi bi-image"></i> รูปภาพ
                @elseif($latest->type === 'sticker')<i class="bi bi-emoji-smile"></i> สติกเกอร์
                @else<i class="bi bi-paperclip"></i> ไฟล์แนบ
                @endif
            </div>
            @else
            <div style="font-size:.82rem;color:#ccc;margin-top:.15rem">เริ่มบทสนทนา</div>
            @endif
        </div>

        {{-- Tenant badge --}}
        @if($conv->tenant)
        <div style="flex-shrink:0">
            <span class="badge-success" style="font-size:.7rem">ผู้เช่า</span>
        </div>
        @endif
    </a>
    @endforeach
    @if($conversations->hasPages())
    <div class="card-footer">{{ $conversations->withQueryString()->links() }}</div>
    @endif
</div>
@endif
@endsection
