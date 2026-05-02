<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงินสำเร็จ</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/scss/app.scss'])
</head>
<body>
<div style="min-height:100vh;background:#F7F5EE;display:flex;align-items:center;justify-content:center;padding:2rem">
    <div style="background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,44,44,.1);width:100%;max-width:420px;overflow:hidden;text-align:center">
        <div style="background:#00A884;padding:2rem">
            <div style="width:72px;height:72px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto">
                <span style="font-size:2rem">✅</span>
            </div>
            <h2 style="color:#fff;margin:.75rem 0 0;font-size:1.3rem">ชำระเงินสำเร็จ!</h2>
        </div>
        <div style="padding:1.75rem">
            <div style="font-size:2rem;font-weight:800;color:#00A884">฿{{ number_format($invoice->total_amount, 2) }}</div>
            <div style="font-size:.85rem;color:#888;margin:.25rem 0 1.5rem">{{ $invoice->invoice_number }}</div>

            <div style="background:#F7F5EE;border-radius:10px;padding:1rem;text-align:left;margin-bottom:1.5rem">
                <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.4rem">
                    <span style="color:#888">ห้อง</span>
                    <span style="font-weight:700;color:#002C2C">{{ $invoice->rental?->room?->room_number }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:.4rem">
                    <span style="color:#888">ผู้เช่า</span>
                    <span style="font-weight:600;color:#002C2C">{{ $invoice->rental?->tenant?->name }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.85rem">
                    <span style="color:#888">วันที่ชำระ</span>
                    <span style="color:#002C2C">{{ $invoice->paid_at?->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <a href="/" style="display:inline-flex;align-items:center;gap:.5rem;background:#002C2C;color:#fff;padding:.65rem 1.5rem;border-radius:10px;font-size:.9rem;font-weight:700;text-decoration:none">
                กลับหน้าหลัก
            </a>
        </div>
    </div>
</div>
</body>
</html>
