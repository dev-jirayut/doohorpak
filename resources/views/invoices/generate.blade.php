@extends('layouts.app')
@section('title', 'ออกใบแจ้งหนี้')

@section('content')
<div class="d-flex align-items-center mb-3 gap-2">
    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0">ออกใบแจ้งหนี้รายเดือน</h5>
</div>

<div class="card" style="max-width:500px">
    <div class="card-body">
        <form method="GET" action="{{ route('invoices.generate-form') }}" class="row g-2 mb-4">
            <div class="col">
                <label class="form-label fw-semibold">เดือน</label>
                <select name="month" class="form-select">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <label class="form-label fw-semibold">ปี</label>
                <select name="year" class="form-select">
                    @foreach(range(now()->year, now()->year - 3, -1) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto d-flex align-items-end">
                <button class="btn btn-secondary">ตรวจสอบ</button>
            </div>
        </form>

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            พบมิเตอร์ที่บันทึกแล้ว: <strong>{{ $readingsCount }} ห้อง</strong>
            @if($existingCount > 0)
                <br><small class="text-muted">มีใบแจ้งหนี้แล้ว {{ $existingCount }} ใบ (จะถูกข้าม)</small>
            @endif
        </div>

        @if($readingsCount > 0)
        <form method="POST" action="{{ route('invoices.generate') }}">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <button class="btn btn-success w-100">
                <i class="bi bi-receipt me-2"></i>
                ออกใบแจ้งหนี้สำหรับเดือน {{ $month }}/{{ $year }}
                ({{ $readingsCount - $existingCount }} ใบ)
            </button>
        </form>
        @else
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            ยังไม่มีข้อมูลมิเตอร์สำหรับเดือนนี้ กรุณา<a href="{{ route('meter-readings.index', ['month' => $month, 'year' => $year]) }}">บันทึกมิเตอร์</a>ก่อน
        </div>
        @endif
    </div>
</div>
@endsection
