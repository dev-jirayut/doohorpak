@extends('layouts.app')
@section('title', 'อัตราค่าสาธารณูปโภค')

@section('content')
<h5 class="mb-3"><i class="bi bi-gear me-2"></i>ตั้งค่าอัตราค่าสาธารณูปโภค</h5>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">กำหนดอัตราใหม่</div>
            <div class="card-body">
                @if($current)
                <div class="alert alert-info small">
                    <strong>อัตราปัจจุบัน:</strong><br>
                    ค่าไฟ: {{ number_format($current->electricity_rate, 4) }} บ./หน่วย<br>
                    ค่าน้ำ: {{ number_format($current->water_rate, 4) }} บ./หน่วย<br>
                    มีผลตั้งแต่: {{ $current->effective_from->format('d/m/Y') }}
                </div>
                @endif
                <form method="POST" action="{{ route('settings.rates.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-lightning-charge text-warning me-1"></i>ราคาค่าไฟต่อหน่วย (บาท)
                            <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="electricity_rate" class="form-control @error('electricity_rate') is-invalid @enderror"
                            value="{{ old('electricity_rate', $current?->electricity_rate ?? 7) }}"
                            min="0" step="0.0001">
                        @error('electricity_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-droplet text-info me-1"></i>ราคาค่าน้ำต่อหน่วย (บาท)
                            <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="water_rate" class="form-control @error('water_rate') is-invalid @enderror"
                            value="{{ old('water_rate', $current?->water_rate ?? 18) }}"
                            min="0" step="0.0001">
                        @error('water_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">มีผลตั้งแต่วันที่ <span class="text-danger">*</span></label>
                        <input type="date" name="effective_from" class="form-control @error('effective_from') is-invalid @enderror"
                            value="{{ old('effective_from', date('Y-m-d')) }}">
                        @error('effective_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>บันทึกอัตราใหม่</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header">ประวัติอัตราค่าสาธารณูปโภค</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr><th>มีผลตั้งแต่</th><th class="text-end">ค่าไฟ/หน่วย</th><th class="text-end">ค่าน้ำ/หน่วย</th><th>สถานะ</th></tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                        <tr>
                            <td>{{ $rate->effective_from->format('d/m/Y') }}</td>
                            <td class="text-end">{{ number_format($rate->electricity_rate, 4) }} บ.</td>
                            <td class="text-end">{{ number_format($rate->water_rate, 4) }} บ.</td>
                            <td>
                                @if($rate->is_active)
                                    <span class="badge bg-success">ใช้งานอยู่</span>
                                @else
                                    <span class="badge bg-secondary">เก่า</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">ยังไม่มีการตั้งค่า</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rates->hasPages())
            <div class="card-footer">{{ $rates->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
