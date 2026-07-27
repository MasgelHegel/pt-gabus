@extends('portal.layout')
@section('title','Katalog Produk')

@section('content')

<div x-data="{
    search: '',
    cart: [],
    cartKey: 'cart_' + {{ auth()->id() ?? 'null' }},
    init() {
        // Jika tidak ada user login, tidak perlu cart
        if (!{{ auth()->id() ?? 'null' }}) {
            this.cart = [];
            return;
        }
        // Bersihkan cart jika baru saja selesai order
        if (sessionStorage.getItem('order_just_placed')) {
            localStorage.removeItem(this.cartKey);
            sessionStorage.removeItem('order_just_placed');
            this.cart = [];
            return;
        }
        // Load cart milik user ini saja
        this.cart = JSON.parse(localStorage.getItem(this.cartKey) || '[]');
    },
    saveCart() {
        localStorage.setItem(this.cartKey, JSON.stringify(this.cart));
    },
    addToCart(id, name, price, unit) {
        const idx = this.cart.findIndex(i => i.id === id);
        if (idx >= 0) { this.cart[idx].qty++; }
        else { this.cart.push({ id, name, price, unit, qty: 1 }); }
        this.saveCart();
        window.toast(name + ' ditambahkan ke keranjang', 'success');
    },
    cartTotal() { return this.cart.reduce((s,i) => s + i.price*i.qty, 0); },
    cartCount() { return this.cart.reduce((s,i) => s + i.qty, 0); },
    removeItem(id) {
        this.cart = this.cart.filter(i => i.id !== id);
        this.saveCart();
    }
}">

{{-- Hero Banner --}}
<div class="mb-8 overflow-hidden rounded-2xl shadow-md relative"
     style="aspect-ratio: 1200/675;">
    <img src="{{ asset('image/banner.png') }}"
         alt="Banner PT Gabus Gas Trusss"
         class="absolute inset-0 w-full h-full object-cover object-center">

    {{-- Search bar overlay --}}
    <div class="absolute bottom-0 left-0 right-0 px-4 pb-4 pt-12 sm:px-6 sm:pb-5
                bg-gradient-to-t from-black/55 via-black/20 to-transparent">
        <div class="max-w-sm flex items-center gap-2.5 rounded-xl
                    bg-white/20 px-3.5 py-2 ring-1 ring-white/30 backdrop-blur-sm">
            <svg class="h-4 w-4 flex-shrink-0 text-white/70"
                 fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z"/>
            </svg>
            <input x-model="search" type="text" placeholder="Cari produk..."
                   class="flex-1 border-0 bg-transparent text-sm text-white
                          placeholder-white/60 focus:outline-none focus:ring-0">
        </div>
    </div>
</div>

{{-- Categories --}}
<div class="mb-6 flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
    <a href="{{ route('portal.catalog') }}" class="flex-shrink-0 rounded-full bg-blue-600 px-4 py-1.5 text-sm font-medium text-white shadow-sm">Semua</a>
    @foreach($categories as $cat)
    <a href="{{ route('portal.catalog') }}?category={{ $cat->id }}" class="flex-shrink-0 rounded-full border border-gray-200 bg-white px-4 py-1.5 text-sm font-medium text-gray-600 hover:border-blue-300 hover:text-blue-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">{{ $cat->name }}</a>
    @endforeach
</div>

{{-- Products Grid --}}
<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
    @forelse($products as $product)
    <div class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
         x-show="!search || '{{ strtolower($product->name) }}'.includes(search.toLowerCase())">
        <div class="relative aspect-square overflow-hidden bg-gray-100 dark:bg-gray-800">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                 class="h-full w-full object-cover transition group-hover:scale-105"
                 onerror="this.src='https://via.placeholder.com/300?text=No+Image'">
            @if($product->stock <= $product->min_stock)
            <span class="absolute right-2 top-2 rounded-full bg-amber-500 px-2 py-0.5 text-xs font-semibold text-white">Stok Tipis</span>
            @endif
        </div>
        <div class="p-3">
            <p class="text-xs text-blue-600 dark:text-blue-400">{{ $product->category?->name ?? '—' }}</p>
            <h3 class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-white line-clamp-2">{{ $product->name }}</h3>
            <p class="mt-1 text-xs text-gray-500">Stok: {{ number_format($product->stock) }} {{ $product->unit }}</p>
            <div class="mt-2 flex items-center justify-between">
                <span class="text-sm font-bold text-blue-600">Rp {{ number_format($product->sell_price, 0, ',', '.') }}</span>
                <button @click="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->sell_price }}, '{{ $product->unit }}')"
                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full py-16 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        <p class="mt-3 text-gray-400">Belum ada produk tersedia</p>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
<div class="mt-8">{{ $products->links() }}</div>

{{-- Floating Cart --}}
<div x-show="cart.length > 0" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="fixed bottom-20 right-4 z-40 sm:bottom-6">
    <div x-data="{ cartOpen: false }">
        <button @click="cartOpen = !cartOpen"
                class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 shadow-lg shadow-blue-500/30 text-white hover:bg-blue-700 transition">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
            <span class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-xs font-bold" x-text="cartCount()"></span>
        </button>

        {{-- Cart Panel --}}
        <div x-show="cartOpen" @click.away="cartOpen=false" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="absolute bottom-16 right-0 w-80 rounded-2xl border border-gray-100 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                <h3 class="font-semibold text-gray-900 dark:text-white">Keranjang (<span x-text="cart.length"></span>)</h3>
                <button @click="cart=[]; localStorage.removeItem('cart')" class="text-xs text-rose-500 hover:underline">Kosongkan</button>
            </div>
            <div class="max-h-64 overflow-y-auto p-2">
                <template x-for="item in cart" :key="item.id">
                    <div class="flex items-center gap-3 rounded-xl p-2 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <div class="flex-1 min-w-0">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white" x-text="item.name"></p>
                            <p class="text-xs text-gray-500">Rp <span x-text="item.price.toLocaleString('id-ID')"></span> × <span x-text="item.qty"></span></p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="item.qty > 1 ? item.qty-- : removeItem(item.id); localStorage.setItem('cart', JSON.stringify(cart))" class="flex h-6 w-6 items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700">−</button>
                            <span class="w-6 text-center text-xs font-semibold" x-text="item.qty"></span>
                            <button @click="item.qty++; localStorage.setItem('cart', JSON.stringify(cart))" class="flex h-6 w-6 items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700">+</button>
                        </div>
                    </div>
                </template>
            </div>
            <div class="border-t border-gray-100 p-3 dark:border-gray-800">
                <div class="mb-2 flex justify-between text-sm">
                    <span class="text-gray-500">Total</span>
                    <span class="font-bold text-blue-600">Rp <span x-text="cartTotal().toLocaleString('id-ID')"></span></span>
                </div>
                @auth
                <a :href="'{{ route('portal.orders.create') }}?cart=' + encodeURIComponent(JSON.stringify(cart))"
                   class="block w-full rounded-xl bg-blue-600 py-2 text-center text-sm font-semibold text-white hover:bg-blue-700 transition">
                    Buat Order
                </a>
                @else
                <a href="{{ route('portal.login') }}" class="block w-full rounded-xl bg-blue-600 py-2 text-center text-sm font-semibold text-white hover:bg-blue-700">
                    Login untuk Order
                </a>
                @endauth
            </div>
        </div>
    </div>
</div>

</div>
@endsection
