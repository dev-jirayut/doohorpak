<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Sarabun', sans-serif;
            background: #F7F5EE;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #002C2C;
        }
        .error-wrap {
            text-align: center;
            padding: 2rem 1rem;
            max-width: 480px;
        }
        .error-code {
            font-size: 7rem;
            font-weight: 800;
            line-height: 1;
            color: #00A884;
            letter-spacing: -4px;
            margin-bottom: .5rem;
        }
        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: .75rem; }
        p  { color: #666; font-size: .95rem; line-height: 1.7; margin-bottom: 1.75rem; }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #00A884;
            color: #fff;
            padding: .65rem 1.5rem;
            border-radius: 10px;
            font-size: .9rem;
            font-weight: 700;
            text-decoration: none;
            transition: background .15s;
        }
        .btn-back:hover { background: #007A60; color: #fff; }
        .decoration {
            position: fixed;
            border-radius: 50%;
            background: #A1FFD1;
            opacity: .25;
            z-index: -1;
        }
        .deco-1 { width: 400px; height: 400px; top: -100px; right: -100px; }
        .deco-2 { width: 250px; height: 250px; bottom: -80px; left: -60px; }
    </style>
</head>
<body>
    <div class="decoration deco-1"></div>
    <div class="decoration deco-2"></div>

    <div class="error-wrap">
        <div class="error-code">@yield('code')</div>
        <div class="error-icon">@yield('icon')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}" class="btn-back">
            <i class="bi bi-arrow-left"></i> กลับหน้าก่อน
        </a>
        &nbsp;
        <a href="{{ route('dashboard') }}" class="btn-back" style="background:#002C2C">
            <i class="bi bi-house"></i> หน้าหลัก
        </a>
    </div>
</body>
</html>
