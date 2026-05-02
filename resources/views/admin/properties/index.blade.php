@extends('layouts.app')
@section('title', 'จัดการที่พัก')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">จัดการที่พัก</h5>
    <a href="{{ route('admin.properties.create') }}" class="btn btn-dark btn-sm">
        <i class="bi bi-plus-lg me-1"></i>เพิ่มที่พัก
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    @forelse($properties as $property)
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="fw-bold mb-0">{{ $property->name }}</h6>
                        <small class="text-muted">
                            @if($property->type === 'hotel')
                                <i class="bi bi-building me-1"></i>โรงแรม (รายวัน)
                            @else
                                <i class="bi bi-house me-1"></i>หอพัก (รายเดือน)
                            @endif
                        </small>
                    </div>
                    @if($property->is_active)
                        <span class="badge bg-success">ใช้งาน</span>
                    @else
                        <span class="badge bg-secondary">ปิด</span>
                    @endif
                </div>
                @if($property->address)
                    <p class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i>{{ $property->address }}</p>
                @endif
                @if($property->phone)
                    <p class="text-muted small mb-2"><i class="bi bi-telephone me-1"></i>{{ $property->phone }}</p>
                @endif
                <div class="d-flex gap-3 text-muted small mb-3">
                    <span><i class="bi bi-door-open me-1"></i>{{ $property->rooms_count }} ห้อง</span>
                    <span><i class="bi bi-people me-1"></i>{{ $property->users->count() }} ผู้ใช้งาน</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil me-1"></i>แก้ไข
                    </a>
                    <form method="POST" action="{{ route('admin.properties.destroy', $property) }}"
                        onsubmit="return confirm('ลบ {{ $property->name }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card"><div class="card-body text-center text-muted py-5">ยังไม่มีที่พัก</div></div>
    </div>
    @endforelse
</div>
@if($properties->hasPages())
<div class="card-footer" style="margin-top:1rem">{{ $properties->withQueryString()->links() }}</div>
@endif
@endsection
