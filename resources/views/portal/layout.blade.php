<!DOCTYPE html>
<html lang="id"
      x-data="{ mobileMenu: false, cartCount: 0 }"
      x-init="darkMode.init()"
      class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3b82f6">
    <title>@yield('title', 'Portal Customer') — {{ config('app.name') }}</title>

    {{-- PWA --}}
    <link rel="manifest" href="/build/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <style>[x-cloak]{display:none!important}</style>
    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 font-sans antialiased dark:bg-gray-950">

{{-- ── TOP NAV ── --}}
<nav class="sticky top-0 z-40 border-b border-white/20 bg-white/80 backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/80">
    <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4 sm:px-6">

        {{-- Logo --}}
        <a href="{{ route('portal.orders.index') }}" class="flex items-center gap-2">
            <img src="{{ asset('image/logo.jpg') }}" alt="Logo" class="h-8 w-auto">
        </a>

        {{-- Desktop Nav --}}
        <div class="hidden items-center gap-1 sm:flex">
            @auth
            <a href="{{ route('portal.orders.index') }}" class="rounded-xl px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 {{ request()->routeIs('portal.orders.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : '' }}">
                Pesanan
            </a>
            <a href="{{ route('portal.invoices.index') }}" class="rounded-xl px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 {{ request()->routeIs('portal.invoices.*') ? 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' : '' }}">
                Invoice
            </a>
            @endauth
        </div>

        {{-- Right Actions --}}
        <div class="flex items-center gap-2">
            {{-- Dark Mode --}}
            <button @click="darkMode.toggle()" class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                </svg>
            </button>

            @auth
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full ring-2 ring-blue-500/30">
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">
                </button>
                <div x-show="open" @click.away="open = false" x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 mt-2 w-52 rounded-2xl border border-gray-100 bg-white py-1 shadow-xl dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 px-4 py-2.5 dark:border-gray-800">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('portal.orders.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                        Pesanan Saya
                    </a>
                    <a href="{{ route('portal.invoices.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        Invoice
                    </a>
                    <div class="border-t border-gray-100 dark:border-gray-800 mt-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15"/></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @else
            <div class="flex items-center gap-2">
                <a href="{{ route('portal.login') }}" class="rounded-xl border border-blue-600 px-4 py-1.5 text-sm font-semibold text-blue-600 hover:bg-blue-50 transition">
                    Login
                </a>
                <a href="{{ route('register') }}" class="rounded-xl bg-blue-600 px-4 py-1.5 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                    Daftar
                </a>
            </div>
            @endauth

            {{-- Mobile menu button --}}
            <button @click="mobileMenu = !mobileMenu" class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 sm:hidden">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileMenu" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="border-t border-gray-100 bg-white px-4 pb-3 pt-2 dark:border-gray-800 dark:bg-gray-900 sm:hidden">
        <div class="flex flex-col gap-1">
            @auth
            <a href="{{ route('portal.orders.index') }}" @click="mobileMenu=false" class="rounded-xl px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Pesanan Saya</a>
            <a href="{{ route('portal.invoices.index') }}" @click="mobileMenu=false" class="rounded-xl px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Invoice</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Flash Messages --}}
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-2"
     class="fixed right-4 top-16 z-50 flex max-w-sm items-center gap-3 rounded-2xl bg-white p-4 shadow-xl ring-1 ring-emerald-500/20 dark:bg-gray-900">
    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
        <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
    </div>
    <p class="flex-1 text-sm font-medium text-gray-900 dark:text-white">{{ session('success') }}</p>
    <button @click="show=false" class="text-gray-400 hover:text-gray-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
</div>
@endif

@if($errors->any())
<div class="fixed right-4 top-16 z-50 max-w-sm rounded-2xl bg-white p-4 shadow-xl ring-1 ring-rose-500/20 dark:bg-gray-900">
    <div class="flex items-start gap-3">
        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/30">
            <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </div>
        <div>
            @foreach($errors->all() as $err)
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $err }}</p>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Page Content --}}
<main class="mx-auto max-w-7xl px-4 pb-24 pt-6 sm:px-6 lg:pb-8">
    @yield('content')
</main>

{{-- Mobile Bottom Nav (only for authenticated customers) --}}
@auth
<nav class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-200 bg-white/90 backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/90 sm:hidden">
    <div class="grid grid-cols-4 px-2 pt-2 pb-safe">
        <a href="{{ route('portal.orders.index') }}" class="flex flex-col items-center gap-0.5 rounded-xl p-2 {{ request()->routeIs('portal.orders.index') ? 'text-blue-600' : 'text-gray-400' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
            <span class="text-xs font-medium">Pesanan</span>
        </a>
        <a href="{{ route('portal.orders.create') }}" class="flex flex-col items-center gap-0.5 rounded-xl p-2 text-gray-400">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span class="text-xs font-medium">Order</span>
        </a>
        <a href="{{ route('portal.orders.index') }}" class="flex flex-col items-center gap-0.5 rounded-xl p-2 {{ request()->routeIs('portal.orders.*') ? 'text-blue-600' : 'text-gray-400' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
            <span class="text-xs font-medium">Pesanan</span>
        </a>
        <a href="{{ route('portal.invoices.index') }}" class="flex flex-col items-center gap-0.5 rounded-xl p-2 {{ request()->routeIs('portal.invoices.*') ? 'text-blue-600' : 'text-gray-400' }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
            <span class="text-xs font-medium">Invoice</span>
        </a>
    </div>
</nav>
@endauth

@livewireScripts

@auth
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.Echo === 'undefined') return;

    const customerId = {{ auth()->user()->customer?->id ?? 'null' }};
    if (! customerId) return;

    window.Echo.channel(`customer.${customerId}`)
        .listen('.order.status_changed', (e) => {
            const msg = `Status pesanan ${e.order_number}: ${e.new_status_label}`;
            window.toast?.(msg, 'info') ?? alert(msg);
            if (window.location.href.includes('/portal/orders')) {
                setTimeout(() => location.reload(), 2000);
            }
        })
        .listen('.payment.verified', (e) => {
            const msg = `Pembayaran ${e.payment_number} telah diverifikasi!`;
            window.toast?.(msg, 'success') ?? alert(msg);
            if (window.location.href.includes('/portal/invoices')) {
                setTimeout(() => location.reload(), 2000);
            }
        });
});
</script>
@endauth
</body>
</html>
