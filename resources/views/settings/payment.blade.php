@extends('layouts.app')
@section('title', 'ตั้งค่าการชำระเงิน')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-credit-card me-2" style="color:#00A884"></i>ตั้งค่าการชำระเงิน</h1>
</div>

<div style="max-width:640px;display:flex;flex-direction:column;gap:1rem">

    {{-- Bank / PromptPay --}}
    <div class="card">
        <div class="card-header"><h5>บัญชีรับเงิน</h5></div>
        <form method="POST" action="{{ route('settings.payment.store') }}" style="padding:1.5rem">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label class="form-label">ชื่อบัญชี</label>
                    <input type="text" name="bank_account_name" class="form-control"
                        value="{{ old('bank_account_name', $property?->bank_account_name) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">ธนาคาร</label>
                    <select name="bank_name" class="form-select">
                        <option value="">— เลือกธนาคาร —</option>
                        @foreach(['กสิกรไทย','กรุงเทพ','ไทยพาณิชย์','กรุงไทย','กรุงศรี','ทหารไทยธนชาต','ออมสิน'] as $bank)
                        <option {{ old('bank_name', $property?->bank_name) === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">เลขที่บัญชี</label>
                <input type="text" name="bank_account_number" class="form-control"
                    value="{{ old('bank_account_number', $property?->bank_account_number) }}" placeholder="000-0-00000-0">
            </div>

            <div class="form-group">
                <label class="form-label">หมายเลขพร้อมเพย์ (เบอร์โทรหรือเลขบัตรประชาชน)</label>
                <input type="text" name="promptpay_id" class="form-control"
                    value="{{ old('promptpay_id', $property?->promptpay_id) }}" placeholder="0812345678">
            </div>

            <div style="border-top:1px solid rgba(0,44,44,.07);margin-bottom:1.25rem;padding-top:1.25rem">
                <div style="font-size:.9rem;font-weight:700;color:#002C2C;margin-bottom:.75rem">รูปแบบรายได้ Platform</div>

                <div style="display:flex;flex-direction:column;gap:.75rem">
                    <label style="display:flex;align-items:flex-start;gap:.75rem;cursor:pointer;padding:.85rem;border-radius:10px;border:2px solid {{ old('revenue_model', $property?->revenue_model) === 'percentage' ? '#00A884' : 'rgba(0,44,44,.1)' }}" onclick="selectRevModel('percentage', this)">
                        <input type="radio" name="revenue_model" value="percentage" id="rev-pct"
                            {{ old('revenue_model', $property?->revenue_model ?? 'percentage') === 'percentage' ? 'checked' : '' }}
                            style="width:16px;height:16px;accent-color:#00A884;margin-top:.15rem;flex-shrink:0">
                        <div>
                            <div style="font-weight:700;font-size:.9rem">หัก % ต่อการชำระ</div>
                            <div style="font-size:.8rem;color:#888;margin-top:.2rem">Platform หัก % จากทุกรายการชำระ</div>
                            <input type="number" name="revenue_percentage" id="pct-input" step="0.01" min="0" max="100"
                                class="form-control" style="margin-top:.5rem;max-width:120px"
                                value="{{ old('revenue_percentage', $property?->revenue_percentage ?? 5) }}"
                                placeholder="5.00">
                        </div>
                    </label>

                    <label style="display:flex;align-items:flex-start;gap:.75rem;cursor:pointer;padding:.85rem;border-radius:10px;border:2px solid {{ old('revenue_model', $property?->revenue_model) === 'package' ? '#00A884' : 'rgba(0,44,44,.1)' }}" onclick="selectRevModel('package', this)">
                        <input type="radio" name="revenue_model" value="package" id="rev-pkg"
                            {{ old('revenue_model', $property?->revenue_model) === 'package' ? 'checked' : '' }}
                            style="width:16px;height:16px;accent-color:#00A884;margin-top:.15rem;flex-shrink:0">
                        <div>
                            <div style="font-weight:700;font-size:.9rem">แพ็กเกจรายห้องต่อเดือน</div>
                            <div style="font-size:.8rem;color:#888;margin-top:.2rem">คิดค่าบริการต่อห้องต่อเดือน</div>
                            <div style="display:flex;align-items:center;gap:.5rem;margin-top:.5rem">
                                <input type="number" name="revenue_package_per_room" step="1" min="0"
                                    class="form-control" style="max-width:100px"
                                    value="{{ old('revenue_package_per_room', $property?->revenue_package_per_room ?? 50) }}"
                                    placeholder="50">
                                <span style="font-size:.82rem;color:#888">บาท/ห้อง/เดือน</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึก</button>
        </form>
    </div>
</div>

<script>
function selectRevModel(val, label) {
    document.querySelectorAll('label[onclick^="selectRevModel"]').forEach(l => {
        l.style.borderColor = 'rgba(0,44,44,.1)';
    });
    label.style.borderColor = '#00A884';
}
</script>
@endsection
