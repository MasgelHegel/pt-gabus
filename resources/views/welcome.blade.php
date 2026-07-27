<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PT Gabus Gas Trusss</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(148,163,184,.15) 1px, transparent 1px),
                linear-gradient(90deg, rgba(148,163,184,.15) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none; z-index: 0;
        }

        body::after {
            content: '';
            position: fixed; top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #6366f1, #3b82f6);
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
            z-index: 10;
        }

        @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

        .page {
            position: relative; z-index: 1;
            width: 100%; max-width: 540px;
            display: flex; flex-direction: column; align-items: center;
        }

        /* ── Brand ── */
        .brand {
            display: flex; flex-direction: column;
            align-items: center; text-align: center;
            margin-bottom: 1.5rem;
        }

        .brand-logo {
            width: 80px; height: 80px;
            border-radius: 20px; overflow: hidden;
            background: #fff;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,.06), 0 10px 28px rgba(59,130,246,.14);
            margin-bottom: .85rem;
        }

        .brand-logo img { width: 100%; height: 100%; object-fit: cover; }

        .brand-name {
            font-size: clamp(1.35rem, 4.5vw, 1.75rem);
            font-weight: 800; letter-spacing: -.025em; color: #0f172a;
        }

        .brand-name span { color: #3b82f6; }

        .brand-pt {
            font-size: .72rem; font-weight: 600;
            color: #64748b; letter-spacing: .05em;
            text-transform: uppercase; margin-top: .3rem;
        }

        .tagline {
            font-size: .875rem; color: #64748b;
            margin-bottom: 1.5rem;
        }

        /* ── CTA Pesan Gas ── */
        .btn-order {
            display: inline-flex; align-items: center;
            gap: .55rem; margin-bottom: 2rem;
            padding: .85rem 2rem;
            border-radius: 14px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: #fff; font-weight: 700; font-size: 1rem;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(99,102,241,.35);
            transition: opacity .15s, transform .1s, box-shadow .15s;
        }

        .btn-order:hover { opacity: .9; box-shadow: 0 6px 28px rgba(99,102,241,.45); }
        .btn-order:active { transform: scale(.97); }

        .btn-order svg { width: 18px; height: 18px; }

        /* ── Divider ── */
        .divider-label {
            display: flex; align-items: center; gap: .75rem;
            width: 100%; margin-bottom: 1.25rem;
        }

        .divider-label hr {
            flex: 1; border: none;
            border-top: 1px solid #e2e8f0;
        }

        .divider-label span {
            font-size: .72rem; font-weight: 600;
            color: #94a3b8; text-transform: uppercase;
            letter-spacing: .07em; white-space: nowrap;
        }

        /* ── Staff Cards ── */
        .cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .75rem; width: 100%;
        }

        @media (max-width: 380px) {
            .cards { grid-template-columns: 1fr; }
        }

        .card {
            display: flex; flex-direction: column;
            gap: .35rem; padding: 1.1rem 1rem 1rem;
            border-radius: 16px;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            text-decoration: none; color: inherit;
            position: relative; overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 10px rgba(0,0,0,.04);
            transition: transform .18s, border-color .18s, box-shadow .18s;
            -webkit-tap-highlight-color: transparent;
        }

        .card::before {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--ca), var(--cb));
            opacity: 0; transition: opacity .18s;
        }

        .card:hover::before { opacity: 1; }

        .card:hover {
            transform: translateY(-3px);
            border-color: rgba(var(--cr), .35);
            box-shadow: 0 4px 6px rgba(0,0,0,.06), 0 10px 24px rgba(var(--cr), .14);
        }

        .card:active { transform: scale(.97); }

        .c-green { --ca:#10b981; --cb:#059669; --cr:16,185,129; --ci:#10b981; }
        .c-amber  { --ca:#f59e0b; --cb:#d97706; --cr:245,158,11; --ci:#f59e0b; }
        .c-rose   { --ca:#f43f5e; --cb:#e11d48; --cr:244,63,94;  --ci:#f43f5e; }

        .card-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            background: rgba(var(--cr), .09);
            border: 1px solid rgba(var(--cr), .18);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: .2rem;
        }

        .card-icon svg { width: 18px; height: 18px; color: var(--ci); }

        .card-title {
            font-size: .9rem; font-weight: 700; color: #0f172a;
        }

        .card-desc {
            font-size: .7rem; color: #64748b; line-height: 1.5;
        }

        .card-badge {
            display: inline-block; margin-top: .35rem;
            font-size: .62rem; font-weight: 600;
            padding: .18rem .5rem; border-radius: 999px;
            background: rgba(var(--cr), .10); color: var(--ci);
            border: 1px solid rgba(var(--cr), .20);
        }

        .card-arrow {
            position: absolute; right: .8rem; bottom: .8rem;
            color: #cbd5e1; transition: color .18s, transform .18s;
        }

        .card:hover .card-arrow { color: var(--ci); transform: translate(2px,-2px); }
        .card-arrow svg { width: 12px; height: 12px; }

        /* ── Footer ── */
        .footer {
            margin-top: 1.75rem;
            text-align: center; font-size: .7rem; color: #94a3b8;
        }
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
        <div class="brand-pt">Distributor Gas LPG Terpercaya</div>
    </div>

    <p class="tagline">Pesan gas LPG langsung dari distributor resmi</p>

    {{-- CTA Customer --}}
    <a href="{{ route('order.create') }}" class="btn-order">
        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
        </svg>
        Pesan Gas Sekarang
    </a>

    {{-- Divider --}}
    <div class="divider-label" style="width:100%">
        <hr><span>Masuk sebagai staff</span><hr>
    </div>

    {{-- Staff Cards --}}
    <div class="cards">

        {{-- Sales --}}
        <a href="/admin" class="card c-green">
            <div class="card-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                </svg>
            </div>
            <div class="card-title">Sales</div>
            <div class="card-desc">Order masuk & pengiriman</div>
            <span class="card-badge">Admin Panel</span>
            <span class="card-arrow">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
            </span>
        </a>

        {{-- Admin --}}
        <a href="/admin" class="card c-amber">
            <div class="card-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                </svg>
            </div>
            <div class="card-title">Admin</div>
            <div class="card-desc">Invoice & verifikasi bayar</div>
            <span class="card-badge">Admin Panel</span>
            <span class="card-arrow">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
            </span>
        </a>

        {{-- Super Admin --}}
        <a href="/admin" class="card c-rose">
            <div class="card-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                </svg>
            </div>
            <div class="card-title">Super Admin</div>
            <div class="card-desc">Akses penuh sistem</div>
            <span class="card-badge">Admin Panel</span>
            <span class="card-arrow">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                </svg>
            </span>
        </a>

    </div>

    <p class="footer">
        &copy; {{ date('Y') }} PT Gabus Gas Trusss &middot; Enterprise Resource Planning
    </p>

</div>
</body>
</html>
