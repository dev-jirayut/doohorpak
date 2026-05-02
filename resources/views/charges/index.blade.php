@extends('layouts.app')
@section('title', 'ค่าใช้จ่ายเพิ่มเติม')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">ค่าใช้จ่ายเพิ่มเติม</h5>
    <a href="{{ route('charges.create') }}" class="btn btn-dark btn-sm">
        <i class="bi bi-plus-lg me-1"></i>เพิ่มรายการ
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>ชื่อรายการ</th>
                    <th>ประเภท</th>
                    <th class="text-end">ราคา (บาท)</th>
                    <th class="text-center">จำนวนห้อง</th>
                    <th class="text-center">สถานะ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($charges as $charge)
                <tr>
                    <td>
                        <div class="fw-500">{{ $charge->name }}</div>
                        @if($charge->description)
                            <small class="text-muted">{{ $charge->description }}</small>
                        @endif
                    </td>
                    <td>
                        @if($charge->type === 'monthly')
                            <span class="badge bg-primary">รายเดือน</span>
                        @else
                            <span class="badge bg-warning text-dark">รายครั้ง</span>
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($charge->amount, 2) }}</td>
                    <td class="text-center">{{ $charge->rooms_count }} ห้อง</td>
                    <td class="text-center">
                        @if($charge->is_active)
                            <span class="badge bg-success">ใช้งาน</span>
                        @else
                            <span class="badge bg-secondary">ปิดใช้งาน</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('charges.edit', $charge) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="{{ route('charges.destroy', $charge) }}" class="d-inline"
                            onsubmit="return confirm('ยืนยันการลบรายการ {{ $charge->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">ยังไม่มีรายการค่าใช้จ่าย</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($charges->hasPages())
    <div class="card-footer">{{ $charges->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
