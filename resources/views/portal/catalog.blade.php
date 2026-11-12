@extends('portal.layout')
@section('title','Katalog Produk')

@section('content')

<div x-data="{ search: '' }">

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

</div>
@endsection
