<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login Customer — PT Gabus Gas Trusss</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
        }

        /* grid bg */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(148,163,184,.18) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148,163,184,.18) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
            z-index: 0;
        }

        /* top accent */
        body::after {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #6366f1, #3b82f6);
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
            z-index: 10;
        }

        @keyframes shimmer {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .page {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
        }

        /* ── Brand ── */
        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand-logo {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,.06), 0 8px 24px rgba(59,130,246,.13);
            margin-bottom: .85rem;
        }

        .brand-logo img { width: 100%; height: 100%; object-fit: cover; }

        .brand-name {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -.025em;
            color: #0f172a;
            line-height: 1.1;
        }

        .brand-name span { color: #3b82f6; }

        .brand-pt {
            font-size: .72rem;
            font-weight: 600;
            color: #64748b;
            letter-spacing: .05em;
            text-transform: uppercase;
            margin-top: .3rem;
        }

        /* ── Card ── */
        .card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 8px 24px rgba(0,0,0,.06);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            padding: 1.1rem 1.5rem;
        }

        .card-header h2 {
            font-size: .95rem;
            font-weight: 700;
            color: #fff;
        }

        .card-header p {
            font-size: .75rem;
            color: rgba(255,255,255,.75);
            margin-top: .2rem;
        }

        .card-body { padding: 1.5rem; }

        /* ── Alert ── */
        .alert-error {
            display: flex;
            align-items: flex-start;
            gap: .5rem;
            padding: .75rem .9rem;
            border-radius: 10px;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;
            font-size: .8rem;
            margin-bottom: 1.1rem;
        }

        .alert-error svg { flex-shrink: 0; width: 15px; height: 15px; margin-top: 1px; }

        /* ── Fields ── */
        .field { margin-bottom: 1rem; }

        label {
            display: block;
            font-size: .78rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: .4rem;
        }

        .input-wrap { position: relative; }

        .input-ico {
            position: absolute;
            left: .85rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #9ca3af;
        }

        .input-ico svg { width: 15px; height: 15px; }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            color: #0f172a;
            font-size: .875rem;
            font-family: inherit;
            padding: .65rem .9rem .65rem 2.4rem;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
            -webkit-appearance: none;
        }

        input:focus {
            background: #fff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,.12);
        }

        input.err { border-color: #f43f5e; }
        input::placeholder { color: #cbd5e1; }

        .eye-btn {
            position: absolute;
            right: .8rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            display: flex;
            align-items: center;
            padding: .2rem;
            transition: color .15s;
        }

        .eye-btn:hover { color: #6b7280; }
        .eye-btn svg { width: 15px; height: 15px; }

        /* ── Remember ── */
        .remember {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: 1.25rem;
            cursor: pointer;
        }

        .remember input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: #3b82f6;
            cursor: pointer;
        }

        .remember span {
            font-size: .8rem;
            color: #6b7280;
        }

        /* ── Submit ── */
        .btn-submit {
            width: 100%;
            padding: .75rem;
            border-radius: 11px;
            border: none;
            font-size: .9rem;
            font-weight: 700;
            color: #fff;
            font-family: inherit;
            cursor: pointer;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            box-shadow: 0 4px 14px rgba(99,102,241,.30);
            transition: opacity .15s, transform .1s, box-shadow .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            letter-spacing: .01em;
        }

        .btn-submit svg { width: 16px; height: 16px; }
        .btn-submit:hover { opacity: .92; box-shadow: 0 6px 20px rgba(99,102,241,.40); }
        .btn-submit:active { transform: scale(.98); }

        /* ── Back link ── */
        .back-link {
            margin-top: 1.25rem;
            text-align: center;
            font-size: .78rem;
            color: #94a3b8;
        }

        .back-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover { text-decoration: underline; }

        /* ── Demo hint ── */
        .demo {
            margin-top: .85rem;
            padding: .85rem 1rem;
            border-radius: 12px;
            border: 1px dashed #bfdbfe;
            background: #eff6ff;
            font-size: .72rem;
            color: #2563eb;
            text-align: center;
            line-height: 1.8;
        }

        .demo strong { color: #1d4ed8; }
    </style>
</head>
<body>
<div class="page">

    {{-- Brand --}}
    <div class="brand">
        <div class="brand-logo">
            <img src="{{ asset('image/logo.jpg') }}" alt="PT Gabus Gas Trusss">
        </div>
        <div class="brand-name">PT Gabus <span>Gas Trusss</span></div>
        <div class="brand-pt">Enterprise Resource Planning</div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Login Customer</h2>
            <p>Masuk ke Portal Customer untuk melihat order & invoice Anda</p>
        </div>

        <div class="card-body">

            @if($errors->any())
            <div class="alert-error">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('portal.login.post') }}">
                @csrf

                {{-- Email --}}
                <div class="field">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <span class="input-ico">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                            </svg>
                        </span>
                        <input id="email" type="email" name="email"
                            value="{{ old('email') }}"
                            required autocomplete="email" autofocus
                            placeholder="email@perusahaan.com"
                            class="{{ $errors->has('email') ? 'err' : '' }}">
                    </div>
                </div>

                {{-- Password --}}
                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <span class="input-ico">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                            </svg>
                        </span>
                        <input id="pw" type="password" name="password"
                            required autocomplete="current-password"
                            placeholder="••••••••">
                        <button type="button" class="eye-btn" id="eye-btn">
                            <svg id="eye-on" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            <svg id="eye-off" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember --}}
                <label class="remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat saya</span>
                </label>

                <button type="submit" class="btn-submit">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                    </svg>
                    Masuk
                </button>
            </form>

            <div class="back-link">
                Belum punya akun? &nbsp;
                <a href="{{ route('register') }}">Daftar sekarang →</a>
                &nbsp;&middot;&nbsp;
                <a href="{{ route('order.create') }}">Pesan tanpa akun →</a>
            </div>
        </div>
    </div>

    {{-- Demo hint (local only) --}}
    @if(config('app.env') === 'local')
    <div class="demo">
        <strong>Demo:</strong> customer@gabus.test &nbsp;/&nbsp; password
    </div>
    @endif

</div>

<script>
    var pw = document.getElementById('pw');
    var on = document.getElementById('eye-on');
    var off = document.getElementById('eye-off');
    document.getElementById('eye-btn').addEventListener('click', function () {
        var show = pw.type === 'text';
        pw.type = show ? 'password' : 'text';
        on.style.display  = show ? '' : 'none';
        off.style.display = show ? 'none' : '';
    });
</script>

</body>
</html>
