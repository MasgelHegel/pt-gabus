@extends('portal.layout')
@section('title','Buat Order Baru')

@section('content')

@php
    $productsJson = $products->map(fn ($p) => [
        'id'    => $p->id,
        'name'  => $p->name,
        'price' => (float) $p->sell_price,
        'unit'  => $p->unit,
        'stock' => (int) $p->stock,
        'image' => $p->image_url,
    ])->values();
@endphp
<div x-data="{
    cart: [],
    cartKey: 'cart_' + {{ auth()->id() ?? 'null' }},
    search: '',
    toast: { show: false, msg: '' },
    products: {{ $productsJson->toJson() }},
    init() {
        // Override dari URL jika ada (dari katalog → create-order)
        const urlCart = (new URLSearchParams(window.location.search)).get('cart');
        if (urlCart) {
            try {
                this.cart = JSON.parse(decodeURIComponent(urlCart));
                this.saveCart();
            } catch (e) {}
        }
    },
    saveCart() {
        localStorage.setItem(this.cartKey, JSON.stringify(this.cart));
    },
    get cartTotal() { return this.cart.reduce((s,i) => s + i.price*i.qty, 0); },
    get cartCount() { return this.cart.reduce((s,i) => s + i.qty, 0); },
    getQty(id) { const i = this.cart.find(x => x.id === id); return i ? i.qty : 0; },
    filteredProducts() {
        if (!this.search) return this.products;
        const q = this.search.toLowerCase();
        return this.products.filter(p => p.name.toLowerCase().includes(q));
    },
    addItem(id) {
        const p = this.products.find(x => x.id === id);
        if (!p) return;
        const idx = this.cart.findIndex(i => i.id === id);
        if (idx >= 0) {
            if (this.cart[idx].qty < p.stock) { this.cart[idx].qty++; }
        } else { this.cart.push({...p, qty: 1}); }
        this.saveCart();
        this.showToast(p.name + ' ditambahkan');
    },
    removeItem(id) {
        const item = this.cart.find(i => i.id === id);
        this.cart = this.cart.filter(i => i.id !== id);
        this.saveCart();
        if (item) this.showToast(item.name + ' dihapus');
    },
    changeQty(id, val) {
        const idx = this.cart.findIndex(i => i.id === id);
        const p = this.products.find(x => x.id === id);
        if (idx < 0 || !p) return;
        const newQty = Math.max(0, Math.min(p.stock, this.cart[idx].qty + val));
        if (newQty === 0) this.removeItem(id);
        else { this.cart[idx].qty = newQty; this.saveCart(); }
    },
    showToast(msg) {
        this.toast = { show: true, msg };
        setTimeout(() => this.toast.show = false, 2000);
    },
    submitForm() {
        if (this.cart.length === 0) return;

        const f = document.getElementById('order-form');

        // Inject hidden inputs dulu
        this.cart.forEach((item, idx) => {
            const p = document.createElement('input');
            p.type = 'hidden'; p.name = 'items['+idx+'][product_id]'; p.value = item.id;
            f.appendChild(p);
            const q = document.createElement('input');
            q.type = 'hidden'; q.name = 'items['+idx+'][quantity]'; q.value = item.qty;
            f.appendChild(q);
        });

        // Bersihkan cart & tandai sudah order
        localStorage.removeItem(this.cartKey);
        sessionStorage.setItem('order_just_placed', '1');

        f.submit();
    },
}">

{{-- Toast --}}
<div x-show="toast.show" x-cloak x-transition
     class="fixed bottom-20 left-1/2 z-50 -translate-x-1/2 rounded-xl bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-lg dark:bg-gray-700">
    <span x-text="toast.msg"></span>
</div>

<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Buat Order Baru</h1>
    <p class="text-sm text-gray-500">Klik <span class="font-semibold">+</span> untuk menambah produk ke keranjang</p>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- Product Selector --}}
    <div class="lg:col-span-2">
        <div class="mb-4 flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z"/></svg>
            <input x-model="search" type="text" placeholder="Cari produk..." class="flex-1 border-0 bg-transparent text-sm focus:outline-none focus:ring-0 text-gray-900 dark:text-white">
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <template x-for="product in filteredProducts()" :key="product.id">
                <div class="card relative overflow-hidden transition hover:ring-2 hover:ring-blue-400">
                    <div class="aspect-square overflow-hidden rounded-t-xl bg-gray-100 dark:bg-gray-800">
                        <img :src="product.image" :alt="product.name" class="h-full w-full object-cover">
                    </div>
                    <div class="p-3">
                        <p class="text-xs font-medium text-gray-900 dark:text-white line-clamp-2 min-h-[2rem]" x-text="product.name"></p>
                        <p class="text-xs text-blue-600 font-semibold mt-1" x-text="'Rp ' + product.price.toLocaleString('id-ID')"></p>
                        <p class="text-xs text-gray-400 mb-2" x-text="'Stok: ' + product.stock + ' ' + product.unit"></p>

                        <template x-if="getQty(product.id) === 0">
                            <button type="button" @click="addItem(product.id)"
                                    class="flex w-full items-center justify-center gap-1 rounded-lg bg-blue-600 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 transition">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                Tambah
                            </button>
                        </template>
                        <template x-if="getQty(product.id) > 0">
                            <div class="flex items-center justify-between rounded-lg bg-gray-100 px-2 py-1 dark:bg-gray-800">
                                <button type="button" @click="changeQty(product.id, -1)"
                                        class="flex h-7 w-7 items-center justify-center rounded-md bg-white text-gray-600 shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                                </button>
                                <span class="text-sm font-bold text-blue-600" x-text="getQty(product.id)"></span>
                                <button type="button" @click="changeQty(product.id, 1)"
                                        class="flex h-7 w-7 items-center justify-center rounded-md bg-white text-gray-600 shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Cart Summary --}}
    <div class="lg:sticky lg:top-20 lg:self-start">
        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h2 class="font-semibold text-gray-900 dark:text-white">
                    Keranjang
                    <span x-show="cartCount > 0" class="ml-1 rounded-full bg-blue-600 px-2 py-0.5 text-xs text-white" x-text="cartCount"></span>
                </h2>
            </div>

            <div class="p-3">
                <template x-if="cart.length === 0">
                    <p class="py-8 text-center text-sm text-gray-400">Belum ada produk dipilih</p>
                </template>
                <div class="space-y-2">
                    <template x-for="item in cart" :key="item.id">
                        <div class="flex items-center gap-3 rounded-xl bg-gray-50 p-2.5 dark:bg-gray-800">
                            <div class="flex-1 min-w-0">
                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white" x-text="item.name"></p>
                                <p class="text-xs text-blue-600">Rp <span x-text="(item.price*item.qty).toLocaleString('id-ID')"></span></p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="changeQty(item.id, -1)" class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-gray-600 shadow-sm hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300">−</button>
                                <span class="w-6 text-center text-sm font-bold" x-text="item.qty"></span>
                                <button type="button" @click="changeQty(item.id, 1)" class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-gray-600 shadow-sm hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300">+</button>
                                <button type="button" @click="removeItem(item.id)" class="ml-1 flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 hover:bg-rose-50 hover:text-rose-500 dark:hover:bg-rose-900/20">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <template x-if="cart.length > 0">
                <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                    <div class="flex justify-between mb-3">
                        <span class="text-sm text-gray-500">Total</span>
                        <span class="font-bold text-blue-600">Rp <span x-text="cartTotal.toLocaleString('id-ID')"></span></span>
                    </div>
                    <form id="order-form" method="POST" action="{{ route('portal.orders.store') }}" @submit.prevent="submitForm()">
                        @csrf
                        <textarea name="notes" rows="2" placeholder="Catatan order (opsional)" class="mb-3 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                        <button type="submit" class="w-full rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                            Buat Order
                        </button>
                    </form>
                </div>
            </template>
        </div>
    </div>
</div>
</div>
@endsection
