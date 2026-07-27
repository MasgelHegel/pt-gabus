@extends('portal.layout')
@section('title','Akun Belum Terhubung')
@section('content')
<div class="flex min-h-64 flex-col items-center justify-center text-center py-20">
    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 dark:bg-amber-900/30">
        <svg class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126Z"/></svg>
    </div>
    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Akun Belum Terhubung</h2>
    <p class="mt-2 max-w-sm text-sm text-gray-500">Akun Anda belum dihubungkan ke data customer. Hubungi admin untuk mendapatkan akses portal customer.</p>
    <a href="{{ route('portal.orders.index') }}" class="mt-4 rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
        Kembali ke Beranda
    </a>
</div>
@endsection
