<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ไฟล์ใหญ่เกินไป</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 1.5rem;
            font-family: 'Sarabun', sans-serif;
            color: #002C2C;
            background:
                radial-gradient(circle at top left, rgba(161, 255, 209, 0.45), transparent 34rem),
                linear-gradient(135deg, #F7F5EE, #ECFFF6);
        }
        .fallback-card {
            max-width: 520px;
            padding: 2rem;
            border: 1px solid rgba(0, 168, 132, 0.18);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 24px 70px rgba(0, 44, 44, 0.16);
            text-align: center;
        }
        h1 { margin: 0 0 0.5rem; font-size: 1.4rem; }
        p { margin: 0 0 1.25rem; color: rgba(0, 44, 44, 0.68); }
        a, button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 1rem;
            border: 0;
            border-radius: 10px;
            background: #00A884;
            color: #fff;
            font: inherit;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="fallback-card">
        <h1>ไฟล์ใหญ่เกินไป</h1>
        <p>กรุณาเลือกไฟล์ไม่เกิน 10MB ต่อไฟล์ แล้วลองส่งใหม่อีกครั้ง</p>
        <button type="button" onclick="history.back()">กลับไปแก้ไข</button>
    </div>

    <script>
    Swal.fire({
        icon: 'error',
        title: 'ไฟล์ใหญ่เกินไป',
        text: 'กรุณาเลือกไฟล์ไม่เกิน 10MB ต่อไฟล์ แล้วลองส่งใหม่อีกครั้ง',
        confirmButtonText: 'กลับไปแก้ไข',
        customClass: { popup: 'swal-jade' },
    }).then(function() {
        history.back();
    });
    </script>
</body>
</html>
