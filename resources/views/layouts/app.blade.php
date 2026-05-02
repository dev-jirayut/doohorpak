<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'แดชบอร์ด') — {{ $currentProperty?->name ?? (auth()->user()?->isSuperAdmin() ? 'ทุกหอพัก' : config('app.name')) }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Icons & Sweetalert --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    {{-- App SCSS (compiled by Vite) --}}
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>

{{-- ─── Sidebar ──────────────────────────────────────────────────────── --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="logo">
            <img src="{{ asset('icon.png') }}" alt="{{ config('app.name') }}">
            <span>ดูหอพัก <strong>DooHorPak</strong></span>
        </a>

        {{-- Property switcher --}}
        @if($userProperties->count() > 1)
        <div style="position:relative">
            <button onclick="togglePropMenu()" style="background:rgba(161,255,209,.08);border:1px solid rgba(161,255,209,.15);border-radius:8px;padding:.35rem .6rem;cursor:pointer;color:#A1FFD1;font-size:.75rem;display:flex;align-items:center;gap:.35rem">
                <i class="bi bi-shuffle"></i>
            </button>
            <div id="propMenu" style="display:none;position:absolute;right:0;top:calc(100% + .5rem);background:#fff;border-radius:10px;box-shadow:0 8px 24px rgba(0,44,44,.2);min-width:180px;overflow:hidden;z-index:300">
                @if(auth()->user()->isSuperAdmin())
                <form method="POST" action="{{ route('property.switch') }}">
                    @csrf
                    <input type="hidden" name="property_id" value="all">
                    <button type="submit" style="display:block;width:100%;padding:.65rem 1rem;border:none;background:{{ !$currentProperty ? '#f0fdf9' : '#fff' }};color:#002C2C;font-size:.85rem;cursor:pointer;text-align:left;font-family:inherit;transition:background .15s"
                        onmouseover="this.style.background='#f0fdf9'" onmouseout="this.style.background='{{ !$currentProperty ? '#f0fdf9' : '#fff' }}'">
                        @if(!$currentProperty)<i class="bi bi-check2 me-1" style="color:#00A884"></i>@endif
                        ทุกหอพัก
                    </button>
                </form>
                @endif
                @foreach($userProperties as $prop)
                <form method="POST" action="{{ route('property.switch') }}">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $prop->id }}">
                    <button type="submit" style="display:block;width:100%;padding:.65rem 1rem;border:none;background:{{ $currentProperty?->id === $prop->id ? '#f0fdf9' : '#fff' }};color:#002C2C;font-size:.85rem;cursor:pointer;text-align:left;font-family:inherit;transition:background .15s"
                        onmouseover="this.style.background='#f0fdf9'" onmouseout="this.style.background='{{ $currentProperty?->id === $prop->id ? '#f0fdf9' : '#fff' }}'">
                        @if($currentProperty?->id === $prop->id)<i class="bi bi-check2 me-1" style="color:#00A884"></i>@endif
                        {{ $prop->name }}
                    </button>
                </form>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Current property badge --}}
    @if($currentProperty || auth()->user()->isSuperAdmin())
    <div style="padding:.6rem 1rem;margin:.25rem .75rem;background:rgba(0,168,132,.15);border-radius:8px">
        <div style="font-size:.72rem;color:#A1FFD1;font-weight:600;letter-spacing:.03em">PROPERTY</div>
        <div style="font-size:.85rem;color:#fff;font-weight:700;margin-top:.1rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $currentProperty?->name ?? 'ทุกหอพัก' }}</div>
    </div>
    @endif

    <nav style="flex:1;overflow-y:auto;padding:.5rem">
        {{-- OVERVIEW --}}
        <div class="sidebar-section"><label>ภาพรวม</label></div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2 icon"></i> แดชบอร์ด
            </a></li>
        </ul>

        {{-- ROOMS --}}
        <div class="sidebar-section"><label>ห้องพัก</label></div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('rooms.index') }}" class="{{ request()->routeIs('rooms.*') ? 'active' : '' }}">
                <i class="bi bi-door-open icon"></i> ห้องทั้งหมด
            </a></li>
            <li><a href="{{ route('room-types.index') }}" class="{{ request()->routeIs('room-types.*') ? 'active' : '' }}">
                <i class="bi bi-tag icon"></i> ประเภทห้อง
            </a></li>
        </ul>

        {{-- TENANTS --}}
        <div class="sidebar-section"><label>ผู้เช่า</label></div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('tenants.index') }}" class="{{ request()->routeIs('tenants.*') ? 'active' : '' }}">
                <i class="bi bi-people icon"></i> ข้อมูลผู้เช่า
            </a></li>
            <li><a href="{{ route('rentals.index') }}" class="{{ request()->routeIs('rentals.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-person icon"></i> การเช่า
            </a></li>
            <li><a href="{{ route('contracts.index') }}" class="{{ request()->routeIs('contracts.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text icon"></i> สัญญาเช่า
            </a></li>
        </ul>

        {{-- BILLING --}}
        <div class="sidebar-section"><label>บิล & ชำระเงิน</label></div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('meter-readings.index') }}" class="{{ request()->routeIs('meter-readings.*') ? 'active' : '' }}">
                <i class="bi bi-lightning-charge icon"></i> บันทึกมิเตอร์
            </a></li>
            <li><a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                <i class="bi bi-receipt icon"></i> ใบแจ้งหนี้
            </a></li>
            <li><a href="{{ route('charges.index') }}" class="{{ request()->routeIs('charges.*') ? 'active' : '' }}">
                <i class="bi bi-plus-circle icon"></i> ค่าบริการเพิ่มเติม
            </a></li>
        </ul>

        {{-- SERVICES --}}
        <div class="sidebar-section"><label>บริการ</label></div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('maintenance.index') }}" class="{{ request()->routeIs('maintenance.*') ? 'active' : '' }}">
                <i class="bi bi-tools icon"></i> แจ้งซ่อม
            </a></li>
            <li><a href="{{ route('parcels.index') }}" class="{{ request()->routeIs('parcels.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam icon"></i> พัสดุ / จดหมาย
            </a></li>
            <li>
                <a href="{{ route('line-chat.index') }}" class="{{ request()->routeIs('line-chat.*') ? 'active' : '' }}" style="position:relative">
                    <i class="bi bi-chat-text icon"></i> LINE OA Chat
                    @php
                        $unreadCount = \App\Models\LineConversation::when($currentProperty, fn ($q) => $q->where('property_id', $currentProperty->id))
                            ->when(!$currentProperty && !auth()->user()->isSuperAdmin(), fn ($q) => $q->whereRaw('1 = 0'))
                            ->where('has_unread', true)
                            ->count();
                    @endphp
                    @if($unreadCount > 0)
                    <span style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:#00A884;color:#fff;font-size:.6rem;font-weight:700;padding:.1rem .4rem;border-radius:100px;min-width:1.1rem;text-align:center">{{ $unreadCount }}</span>
                    @endif
                </a>
            </li>
        </ul>

        {{-- SETTINGS --}}
        <div class="sidebar-section"><label>ตั้งค่า</label></div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('settings.rates') }}" class="{{ request()->routeIs('settings.rates') ? 'active' : '' }}">
                <i class="bi bi-lightning icon"></i> อัตราค่าสาธารณูปโภค
            </a></li>
            <li><a href="{{ route('settings.line') }}" class="{{ request()->routeIs('settings.line') ? 'active' : '' }}">
                <i class="bi bi-chat-dots icon"></i> LINE Messaging API
            </a></li>
            @if(auth()->user()->isSuperAdmin())
            <li><a href="{{ route('settings.line.rich-menu') }}" class="{{ request()->routeIs('settings.line.rich-menu*') ? 'active' : '' }}">
                <i class="bi bi-grid-3x3-gap icon"></i> LINE Rich Menu
            </a></li>
            @endif
            <li><a href="{{ route('settings.payment') }}" class="{{ request()->routeIs('settings.payment') ? 'active' : '' }}">
                <i class="bi bi-credit-card icon"></i> การชำระเงิน
            </a></li>
        </ul>

        {{-- SUPER ADMIN --}}
        @if(auth()->user()->isSuperAdmin())
        <div class="sidebar-section"><label>Super Admin</label></div>
        <ul class="sidebar-nav">
            <li><a href="{{ route('admin.properties.index') }}" class="{{ request()->routeIs('admin.properties.*') ? 'active' : '' }}">
                <i class="bi bi-building-gear icon"></i> จัดการหอพัก
            </a></li>
            <li><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-shield-person icon"></i> จัดการผู้ใช้
            </a></li>
            <li><a href="{{ route('admin.revenues.index') }}" class="{{ request()->routeIs('admin.revenues.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow icon"></i> รายได้ Platform
            </a></li>
        </ul>
        @endif
    </nav>

    {{-- User footer --}}
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
            <div style="flex:1;min-width:0">
                <div class="name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ auth()->user()->name }}</div>
                <div class="role">{{ auth()->user()->role_label }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="flex-shrink:0">
                @csrf
                <button type="submit" title="ออกจากระบบ" style="background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;padding:.25rem;font-size:1rem;transition:color .15s"
                    onmouseover="this.style.color='#ff6b6b'" onmouseout="this.style.color='rgba(255,255,255,.4)'">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ─── Mobile overlay ───────────────────────────────────────────────── --}}
<div id="sidebarOverlay" onclick="closeSidebar()" style="display:none;position:fixed;inset:0;background:rgba(0,44,44,.5);z-index:99;backdrop-filter:blur(2px)"></div>

{{-- ─── Main wrapper ─────────────────────────────────────────────────── --}}
<div class="main-wrapper">
    {{-- Topbar --}}
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <h6 style="margin:0;font-size:.95rem;font-weight:700;color:#002C2C">@yield('title', 'แดชบอร์ด')</h6>
        </div>
        <div class="topbar-right">
            <span style="font-size:.78rem;color:#888">{{ now()->locale('th')->translatedFormat('d M Y') }}</span>

            @if($currentProperty || auth()->user()->isSuperAdmin())
            <span style="background:rgba(0,168,132,.1);color:#007A60;font-size:.75rem;font-weight:700;padding:.3rem .75rem;border-radius:100px;border:1px solid rgba(0,168,132,.2)">
                <i class="bi bi-house me-1"></i>{{ $currentProperty?->name ?? 'ทุกหอพัก' }}
            </span>
            @endif
        </div>
    </header>

    {{-- Flash alerts --}}
    @if(session('success') || session('error') || session('warning') || session('info'))
    <div style="padding:1rem 1.5rem 0">
        @if(session('success'))
        <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> {{ session('error') }}</div>
        @endif
        @if(session('warning'))
        <div class="alert alert-warning"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('warning') }}</div>
        @endif
        @if(session('info'))
        <div class="alert alert-info"><i class="bi bi-info-circle-fill"></i> {{ session('info') }}</div>
        @endif
    </div>
    @endif

    <main class="page-content">
        @yield('content')
    </main>
</div>

{{-- ─── Scripts ──────────────────────────────────────────────────────── --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
@stack('scripts')

<script>
function toggleSidebar() {
    const sb = document.getElementById('sidebar');
    const ov = document.getElementById('sidebarOverlay');
    sb.classList.toggle('open');
    ov.style.display = sb.classList.contains('open') ? 'block' : 'none';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').style.display = 'none';
}
function togglePropMenu() {
    const m = document.getElementById('propMenu');
    m.style.display = m.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('#propMenu') && !e.target.closest('[onclick="togglePropMenu()"]')) {
        const m = document.getElementById('propMenu');
        if (m) m.style.display = 'none';
    }
});

@if(session('success'))
Swal.fire({ toast:true, position:'top-end', icon:'success', title:@json(session('success')), showConfirmButton:false, timer:3500, timerProgressBar:true, customClass:{popup:'swal-jade'} });
@endif
@if(session('error'))
Swal.fire({ toast:true, position:'top-end', icon:'error', title:@json(session('error')), showConfirmButton:false, timer:4500, timerProgressBar:true, customClass:{popup:'swal-jade'} });
@endif
@if(session('warning'))
Swal.fire({ toast:true, position:'top-end', icon:'warning', title:@json(session('warning')), showConfirmButton:false, timer:4000, timerProgressBar:true, customClass:{popup:'swal-jade'} });
@endif
</script>
<style>
.swal-jade { font-family:'Sarabun',sans-serif !important; font-size:.9rem !important; }
</style>
</body>
</html>
