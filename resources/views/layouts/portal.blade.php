<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Customer') — PT Gabus Gas Trusss</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    {{-- Hapus SEMUA cart dari localStorage secara synchronous sebelum halaman render --}}
    <script>
        (function() {
            try {
                var uid = '{{ auth()->id() }}';
                var keep = uid ? ('cart_' + uid) : '__none__';
                var keys = Object.keys(localStorage);
                for (var i = 0; i < keys.length; i++) {
                    var k = keys[i];
                    if (k.indexOf('cart') !== -1 && k !== keep) {
                        localStorage.removeItem(k);
                    }
                }
            } catch(e) {}
        })();
    </script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col">

    <!-- ══════════════════════════════════════════
         Top info bar — jam & tanggal realtime
    ══════════════════════════════════════════ -->
    <div class="bg-slate-900 border-b border-slate-800/60 px-4 py-1.5"
         x-data="realtime()" x-init="start()">
        <div class="max-w-7xl mx-auto flex items-center justify-between text-xs text-slate-400">

            <!-- Kiri: nama perusahaan -->
            <span class="hidden sm:block font-medium text-slate-500">PT Gabus Gas Trusss &mdash; Customer Portal</span>

            <!-- Kanan: jam + tanggal realtime -->
            <div class="flex items-center gap-3 ml-auto">
                <!-- Jam digital -->
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-blue-400/70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <span class="font-mono text-slate-300 text-xs tracking-widest" x-text="clock"></span>
                </div>

                <span class="text-slate-700">|</span>

                <!-- Tanggal + hari -->
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-blue-400/70" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                    </svg>
                    <span class="text-slate-300" x-text="dateStr"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         Navbar utama
    ══════════════════════════════════════════ -->
    <header class="bg-slate-900/80 backdrop-blur border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

            <!-- Brand -->
            <div class="flex items-center gap-3 flex-shrink-0">
                <img src="{{ asset('image/logo.jpg') }}" alt="Logo"
                     class="w-9 h-9 rounded-xl object-cover border border-slate-700 shadow">
                <div class="hidden sm:block">
                    <a href="{{ route('portal.orders.index') }}" class="font-bold text-base text-white tracking-tight leading-tight">
                        PT Gabus <span class="text-blue-400">Gas Trusss</span>
                    </a>
                    <p class="text-[10px] text-slate-500 leading-tight">Customer Portal</p>
                </div>
            </div>

            <!-- Nav links -->
            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ route('portal.orders.create') }}"
                   class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('portal.orders.create') ? 'bg-blue-600 text-white shadow shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Order
                </a>
                <a href="{{ route('portal.orders.index') }}"
                   class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('portal.orders.*') ? 'bg-blue-600 text-white shadow shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                    Pesanan
                </a>
                <a href="{{ route('portal.invoices.index') }}"
                   class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs('portal.invoices.*') ? 'bg-blue-600 text-white shadow shadow-blue-600/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                    Invoice
                </a>
            </nav>

            <!-- User info + logout -->
            <div class="flex items-center gap-3 flex-shrink-0">
                @auth
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-semibold text-slate-200 leading-tight">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-blue-400 font-medium leading-tight">
                            Piutang: Rp {{ number_format(optional($customer)->piutang_balance ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('portal.logout') }}">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 rounded-lg border border-slate-700 text-xs font-semibold text-slate-300 hover:bg-rose-600 hover:border-rose-600 hover:text-white transition">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('portal.login') }}"
                       class="px-4 py-2 rounded-lg bg-blue-600 text-white font-medium text-sm hover:bg-blue-500 transition">
                        Login
                    </a>
                @endauth
            </div>
        </div>

        <!-- Mobile nav -->
        <div class="md:hidden border-t border-slate-800 flex">
            <a href="{{ route('portal.orders.create') }}"
               class="flex-1 flex flex-col items-center py-2 text-xs font-medium gap-0.5
                      {{ request()->routeIs('portal.orders.create') ? 'text-blue-400' : 'text-slate-500' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Order
            </a>
            <a href="{{ route('portal.orders.index') }}"
               class="flex-1 flex flex-col items-center py-2 text-xs font-medium gap-0.5
                      {{ request()->routeIs('portal.orders.*') ? 'text-blue-400' : 'text-slate-500' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                Pesanan
            </a>
            <a href="{{ route('portal.invoices.index') }}"
               class="flex-1 flex flex-col items-center py-2 text-xs font-medium gap-0.5
                      {{ request()->routeIs('portal.invoices.*') ? 'text-blue-400' : 'text-slate-500' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                Invoice
            </a>
        </div>
    </header>

    <!-- Flash messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full space-y-2">
        @if(session('success'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-950/80 border border-emerald-600/50 text-emerald-300 shadow-lg">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="flex items-center gap-3 p-4 rounded-xl bg-rose-950/80 border border-rose-600/50 text-rose-300 shadow-lg">
            <svg class="w-5 h-5 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif
    </div>

    <!-- Content -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800 py-5 text-center text-xs text-slate-500"
            x-data="realtime()" x-init="start()">
        <div class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-3">
            <span>&copy; <span x-text="year"></span> PT Gabus Gas Trusss. All rights reserved.</span>
            <span class="hidden sm:block text-slate-700">·</span>
            <span class="font-mono text-slate-600" x-text="clock + ' WIB'"></span>
        </div>
    </footer>

    <!-- Alpine.js realtime clock -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('realtime', () => ({
                clock:   '',
                dateStr: '',
                year:    '',

                start() {
                    this.tick();
                    setInterval(() => this.tick(), 1000);
                },

                tick() {
                    const now  = new Date();
                    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                    const months = ['Januari','Februari','Maret','April','Mei','Juni',
                                    'Juli','Agustus','September','Oktober','November','Desember'];

                    const hh = String(now.getHours()).padStart(2,'0');
                    const mm = String(now.getMinutes()).padStart(2,'0');
                    const ss = String(now.getSeconds()).padStart(2,'0');
                    this.clock   = `${hh}:${mm}:${ss}`;
                    this.dateStr = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
                    this.year    = now.getFullYear();
                }
            }));
        });
    </script>

    <!-- Background Realtime Sync for Customer Portal Pages -->
    <script>
        setInterval(() => {
            if (document.hidden) return;

            const active = document.activeElement;
            if (active && (
                active.tagName === 'INPUT' ||
                active.tagName === 'TEXTAREA' ||
                active.tagName === 'SELECT' ||
                active.closest('form')
            )) {
                return;
            }

            const openForms = Array.from(document.querySelectorAll('form')).filter(f => f.offsetWidth > 0 || f.offsetHeight > 0);
            if (openForms.length > 0) {
                return;
            }

            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const currentContent = document.querySelector('main');
                    const newContent = doc.querySelector('main');
                    if (currentContent && newContent) {
                        currentContent.innerHTML = newContent.innerHTML;
                    }

                    const currentPiutang = document.querySelector('.text-right.hidden.sm\\:block');
                    const newPiutang = doc.querySelector('.text-right.hidden.sm\\:block');
                    if (currentPiutang && newPiutang) {
                        currentPiutang.innerHTML = newPiutang.innerHTML;
                    }
                })
                .catch(err => console.warn('Realtime sync failed:', err));
        }, 10000);
    </script>
</body>
</html>
