@extends('layouts.app')
@section('title', 'จัดการผู้ใช้งาน')

@section('content')
<div class="page-header">
    <div>
        <h1><i class="bi bi-people me-2" style="color:#00A884"></i>จัดการผู้ใช้งาน</h1>
        <div class="text-muted small">กำหนดผู้ดูแล เจ้าหน้าที่ และสิทธิ์การเข้าใช้งานระบบ</div>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>เพิ่มผู้ใช้งาน
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($users->isEmpty())
<div class="card">
    <div class="card-body text-center text-muted py-4">ยังไม่มีผู้ใช้งาน</div>
</div>
@else
<div class="user-card-grid">
    @foreach($users as $user)
    <div class="card user-card">
        <div class="user-card-main">
            <div class="user-avatar">
                {{ mb_substr($user->name, 0, 1) }}
            </div>
            <div>
                <h3>{{ $user->name }}</h3>
                <div class="user-email">{{ $user->email }}</div>
            </div>
        </div>

        <div class="user-meta">
            <div>
                <span>บทบาท</span>
                @if($user->role === 'admin')
                    <strong class="badge badge-midnight">แอดมิน</strong>
                @else
                    <strong class="badge badge-secondary">เจ้าหน้าที่</strong>
                @endif
            </div>
            <div>
                <span>สร้างเมื่อ</span>
                <strong>{{ $user->created_at->format('d/m/Y') }}</strong>
            </div>
        </div>

        <div class="user-card-actions">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil me-1"></i>แก้ไข
            </a>
            @if($user->id !== auth()->id())
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                onsubmit="return confirm('ยืนยันการลบผู้ใช้ {{ $user->name }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>ลบ
                </button>
            </form>
            @else
            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                <i class="bi bi-shield-check me-1"></i>บัญชีนี้
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>

@if($users->hasPages())
<div class="card-footer" style="margin-top:1rem">{{ $users->withQueryString()->links() }}</div>
@endif
@endif
@endsection
