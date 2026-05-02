<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ — Dormi</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Sarabun', sans-serif;
    min-height: 100vh;
    background: #002C2C;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

/* Decorative blobs */
.blob {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}
.blob-1 {
    width: 520px; height: 520px;
    background: radial-gradient(circle, rgba(0,168,132,.18) 0%, transparent 70%);
    top: -140px; right: -140px;
}
.blob-2 {
    width: 360px; height: 360px;
    background: radial-gradient(circle, rgba(161,255,209,.08) 0%, transparent 70%);
    bottom: -100px; left: -60px;
}
.blob-3 {
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(0,168,132,.12) 0%, transparent 70%);
    top: 50%; left: 8%;
    transform: translateY(-50%);
}

/* Subtle grid overlay */
body::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(161,255,209,.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(161,255,209,.03) 1px, transparent 1px);
    background-size: 48px 48px;
}

.login-wrap {
    width: 100%;
    max-width: 400px;
    padding: 1.5rem;
    position: relative;
    z-index: 1;
}

/* Brand mark */
.login-logo {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, #00A884, #33C9A0);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.5rem;
    box-shadow: 0 8px 28px rgba(0,168,132,.45);
    font-size: 1.6rem;
}

.login-card {
    background: rgba(0,44,44,.6);
    backdrop-filter: blur(28px) saturate(160%);
    -webkit-backdrop-filter: blur(28px) saturate(160%);
    border: 1px solid rgba(161,255,209,.12);
    border-radius: 20px;
    padding: 2.25rem 2rem 2rem;
    box-shadow:
        0 24px 64px rgba(0,0,0,.35),
        inset 0 1px 0 rgba(161,255,209,.08);
}

.login-title {
    font-size: 1.45rem;
    font-weight: 800;
    color: #fff;
    text-align: center;
    letter-spacing: -.01em;
    margin-bottom: .3rem;
}
.login-sub {
    font-size: .82rem;
    color: rgba(161,255,209,.55);
    text-align: center;
    margin-bottom: 2rem;
}

/* Divider */
.divider {
    height: 1px;
    background: rgba(161,255,209,.1);
    margin: 1.5rem 0;
}

label.lbl {
    display: block;
    font-size: .75rem;
    font-weight: 700;
    color: rgba(161,255,209,.7);
    letter-spacing: .06em;
    text-transform: uppercase;
    margin-bottom: .45rem;
}

.input-wrap {
    position: relative;
    margin-bottom: 1.1rem;
}
.input-wrap .icon {
    position: absolute;
    left: .9rem;
    top: 50%; transform: translateY(-50%);
    color: rgba(161,255,209,.4);
    font-size: .95rem;
    pointer-events: none;
    transition: color .2s;
}
.form-control {
    width: 100%;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(161,255,209,.15);
    border-radius: 10px;
    color: #fff;
    padding: .7rem 1rem .7rem 2.5rem;
    font-family: 'Sarabun', sans-serif;
    font-size: .93rem;
    transition: border-color .2s, background .2s, box-shadow .2s;
    outline: none;
}
.form-control::placeholder { color: rgba(255,255,255,.22); }
.form-control:focus {
    background: rgba(0,168,132,.08);
    border-color: rgba(0,168,132,.6);
    box-shadow: 0 0 0 3px rgba(0,168,132,.12);
}
.form-control:focus + .icon,
.input-wrap:focus-within .icon { color: #00A884; }
.form-control.is-invalid { border-color: #e74c3c; }
.err { color: #ff8888; font-size: .78rem; margin-top: .3rem; display: block; }

/* Remember me */
.remember-row {
    display: flex; align-items: center; gap: .5rem;
    margin-bottom: 1.5rem;
}
.remember-row input[type=checkbox] {
    width: 16px; height: 16px;
    accent-color: #00A884;
    cursor: pointer;
}
.remember-row label {
    font-size: .85rem;
    color: rgba(161,255,209,.55);
    cursor: pointer;
}

/* Submit button */
.btn-login {
    width: 100%;
    background: linear-gradient(135deg, #00A884 0%, #33C9A0 100%);
    border: none;
    border-radius: 10px;
    color: #002C2C;
    font-size: .95rem;
    font-weight: 700;
    font-family: 'Sarabun', sans-serif;
    padding: .75rem 1rem;
    cursor: pointer;
    transition: all .2s;
    box-shadow: 0 4px 18px rgba(0,168,132,.4);
    display: flex; align-items: center; justify-content: center; gap: .45rem;
    letter-spacing: .01em;
}
.btn-login:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(0,168,132,.5);
    background: linear-gradient(135deg, #009e7a 0%, #2abf98 100%);
}
.btn-login:active { transform: translateY(0); box-shadow: none; }

/* Footer */
.login-footer {
    text-align: center;
    margin-top: 1.5rem;
    font-size: .72rem;
    color: rgba(161,255,209,.2);
    letter-spacing: .04em;
}

/* Pulse ring on logo */
@keyframes pulse-ring {
    0%   { transform: scale(1);   opacity: .5; }
    70%  { transform: scale(1.6); opacity: 0; }
    100% { transform: scale(1.6); opacity: 0; }
}
.logo-ring {
    position: absolute;
    width: 60px; height: 60px;
    border-radius: 18px;
    border: 2px solid rgba(0,168,132,.4);
    animation: pulse-ring 2.4s ease-out infinite;
}
.logo-wrap {
    position: relative;
    width: 60px;
    margin: 0 auto 1.5rem;
    display: flex; align-items: center; justify-content: center;
}
</style>
</head>
<body>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>

<div class="login-wrap">
    <div class="logo-wrap">
        <div class="logo-ring"></div>
        <div class="login-logo">🏠</div>
    </div>

    <div class="login-card">
        <div class="login-title">Dormi</div>
        <div class="login-sub">ระบบจัดการหอพัก · เข้าสู่ระบบเพื่อดำเนินการต่อ</div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <label class="lbl" for="email">อีเมล</label>
                <div class="input-wrap">
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        placeholder="your@email.com"
                        autofocus autocomplete="email">
                    <i class="bi bi-envelope icon"></i>
                </div>
                @error('email')<span class="err"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>@enderror
            </div>

            <div style="margin-top:.9rem">
                <label class="lbl" for="password">รหัสผ่าน</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="••••••••"
                        autocomplete="current-password">
                    <i class="bi bi-lock icon"></i>
                </div>
                @error('password')<span class="err"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span>@enderror
            </div>

            <div class="divider"></div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">จดจำการเข้าสู่ระบบ</label>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
            </button>
        </form>
    </div>

    <p class="login-footer">PROPERTY MANAGEMENT SYSTEM · DORMI</p>
</div>
</body>
</html>
