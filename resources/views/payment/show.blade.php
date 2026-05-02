<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงิน — {{ $invoice->invoice_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    @vite(['resources/scss/app.scss'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div class="payment-page">
    <div class="payment-card">
        {{-- Header --}}
        <div class="payment-header">
            <div class="logo">🏠 {{ $invoice->property?->name }}</div>
            <h2>ชำระใบแจ้งหนี้</h2>
            <div style="font-size:.82rem;color:#A1FFD1;margin-top:.25rem">{{ $invoice->invoice_number }}</div>
        </div>

        {{-- Invoice Summary --}}
        <div class="invoice-summary">
            <div style="font-size:.8rem;color:#888;margin-bottom:.25rem">ยอดที่ต้องชำระ</div>
            <div class="total-amount">฿{{ number_format($invoice->total_amount, 2) }}</div>
            <div style="font-size:.82rem;color:#888;margin-top:.5rem;text-align:center">
                ครบกำหนด: {{ $invoice->due_date?->format('d/m/Y') ?? '-' }}
                @if($invoice->due_date?->isPast())
                <span style="color:#e74c3c;font-weight:600"> (เกินกำหนด)</span>
                @endif
            </div>

            {{-- Item breakdown --}}
            <div style="margin-top:1rem;border-top:1px solid rgba(0,44,44,.08);padding-top:.75rem">
                <div style="display:flex;justify-content:space-between;font-size:.82rem;color:#666;margin-bottom:.3rem">
                    <span>ค่าเช่า</span><span>฿{{ number_format($invoice->room_charge, 2) }}</span>
                </div>
                @if($invoice->electricity_charge > 0)
                <div style="display:flex;justify-content:space-between;font-size:.82rem;color:#666;margin-bottom:.3rem">
                    <span>ค่าไฟ ({{ $invoice->electricity_units }} หน่วย)</span>
                    <span>฿{{ number_format($invoice->electricity_charge, 2) }}</span>
                </div>
                @endif
                @if($invoice->water_charge > 0)
                <div style="display:flex;justify-content:space-between;font-size:.82rem;color:#666;margin-bottom:.3rem">
                    <span>ค่าน้ำ ({{ $invoice->water_units }} หน่วย)</span>
                    <span>฿{{ number_format($invoice->water_charge, 2) }}</span>
                </div>
                @endif
                @foreach($invoice->items as $item)
                <div style="display:flex;justify-content:space-between;font-size:.82rem;color:#666;margin-bottom:.3rem">
                    <span>{{ $item->description }}</span><span>฿{{ number_format($item->amount, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Payment Methods --}}
        <div class="payment-methods">
            <div class="method-tabs">
                <button class="active" onclick="switchMethod('promptpay', this)" id="tab-promptpay">
                    <i class="bi bi-qr-code me-1"></i> พร้อมเพย์
                </button>
                <button onclick="switchMethod('card', this)" id="tab-card">
                    <i class="bi bi-credit-card me-1"></i> บัตรเครดิต
                </button>
            </div>

            {{-- PromptPay QR --}}
            <div id="method-promptpay">
                <p style="text-align:center;font-size:.85rem;color:#666;margin-bottom:1rem">
                    กดปุ่มด้านล่างเพื่อสร้าง QR Code สำหรับชำระเงิน
                </p>
                <button class="btn btn-primary btn-block btn-lg" id="btnPromptPay" onclick="createPromptPay()">
                    <i class="bi bi-qr-code-scan me-1"></i> สร้าง QR พร้อมเพย์
                </button>
                <div id="qrContainer" class="qr-container" style="display:none">
                    <div style="font-size:.85rem;color:#666;text-align:center">สแกน QR Code เพื่อชำระเงิน</div>
                    <img id="qrImage" src="" alt="PromptPay QR">
                    <div style="font-size:.78rem;color:#888;text-align:center">QR หมดอายุใน 15 นาที</div>
                    <button class="btn btn-ghost btn-sm" onclick="checkPaymentStatus()">
                        <i class="bi bi-arrow-clockwise me-1"></i>ตรวจสอบสถานะ
                    </button>
                </div>
            </div>

            {{-- Credit Card --}}
            <div id="method-card" style="display:none">
                <div class="form-group">
                    <label class="form-label">หมายเลขบัตร</label>
                    <input type="tel" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                    <div class="form-group">
                        <label class="form-label">MM/YY</label>
                        <input type="tel" class="form-control" id="cardExpiry" placeholder="MM/YY" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label class="form-label">CVV</label>
                        <input type="tel" class="form-control" id="cardCvv" placeholder="123" maxlength="4">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">ชื่อบนบัตร</label>
                    <input type="text" class="form-control" id="cardName" placeholder="ชื่อ นามสกุล">
                </div>
                <button class="btn btn-primary btn-block btn-lg" onclick="payWithCard()">
                    <i class="bi bi-lock-fill me-1"></i>ชำระเงิน ฿{{ number_format($invoice->total_amount, 2) }}
                </button>
                <div style="text-align:center;margin-top:.75rem;font-size:.75rem;color:#888">
                    <i class="bi bi-shield-lock me-1"></i>ปลอดภัยด้วย SSL · ขับเคลื่อนโดย Omise
                </div>
            </div>

            <div id="paymentError" style="display:none" class="alert alert-danger" style="margin-top:1rem"></div>
        </div>
    </div>
</div>

<script src="https://cdn.omise.co/omise.js"></script>
<script>
const invoiceId = {{ $invoice->id }};
const CSRF = document.querySelector('meta[name=csrf-token]').content;

function switchMethod(method, btn) {
    document.getElementById('method-promptpay').style.display = method === 'promptpay' ? 'block' : 'none';
    document.getElementById('method-card').style.display      = method === 'card'      ? 'block' : 'none';
    document.querySelectorAll('.method-tabs button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

async function createPromptPay() {
    const btn = document.getElementById('btnPromptPay');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>กำลังสร้าง QR...';

    const res = await fetch(`/pay/${invoiceId}/promptpay`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
    });
    const data = await res.json();

    if (data.success && data.authorize_uri) {
        document.getElementById('qrImage').src = data.authorize_uri;
        document.getElementById('qrContainer').style.display = 'flex';
        btn.style.display = 'none';
    } else {
        showError(data.message || 'เกิดข้อผิดพลาด');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-qr-code-scan me-1"></i>สร้าง QR พร้อมเพย์';
    }
}

async function checkPaymentStatus() {
    const res = await fetch(`/pay/${invoiceId}/callback`);
    if (res.redirected) window.location = res.url;
}

async function payWithCard() {
    const number = document.getElementById('cardNumber').value.replace(/\s/g, '');
    const expiry = document.getElementById('cardExpiry').value.split('/');
    const cvv    = document.getElementById('cardCvv').value;
    const name   = document.getElementById('cardName').value;

    if (!number || !expiry[0] || !expiry[1] || !cvv || !name) {
        showError('กรุณากรอกข้อมูลบัตรให้ครบถ้วน');
        return;
    }

    Omise.setPublicKey('{{ $omisePublicKey }}');
    Omise.createToken('card', {
        number, expiration_month: expiry[0], expiration_year: '20'+expiry[1],
        security_code: cvv, name
    }, async (statusCode, response) => {
        if (statusCode !== 200) {
            showError(response.message || 'บัตรไม่ถูกต้อง');
            return;
        }
        const res = await fetch(`/pay/${invoiceId}/card`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
            body: JSON.stringify({ token: response.id })
        });
        const data = await res.json();
        if (data.redirect) { window.location = data.redirect; }
        else if (data.authorize_uri) { window.location = data.authorize_uri; }
        else { showError(data.message || 'เกิดข้อผิดพลาด'); }
    });
}

function showError(msg) {
    const el = document.getElementById('paymentError');
    el.style.display = 'flex';
    el.textContent = msg;
}
</script>
</body>
</html>
