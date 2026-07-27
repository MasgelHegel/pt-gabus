@extends('portal.layout')
@section('title','Pesanan Saya')

@section('content')
@if(session('success') && str_contains(session('success'), 'berhasil dibuat'))
<script>
    localStorage.removeItem('cart');
    localStorage.removeItem('cart_backup');
</script>
@endif

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Pesanan Saya</h1>
        <p class="text-sm text-gray-500">Riwayat semua order yang pernah dibuat</p>
    </div>
    <a href="{{ route('portal.orders.create') }}" class="flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Order Baru
    </a>
</div>

@if(session('whatsapp_order_number'))
    @php
        $latestOrder = \App\Models\Order::where('order_number', session('whatsapp_order_number'))->first();
    @endphp
    @if($latestOrder)
    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/30 dark:bg-emerald-900/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                    <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 0 0 1.37 5.054L2 22l5.132-1.347a9.936 9.936 0 0 0 4.88 1.28c5.508 0 9.99-4.478 9.99-9.984A10.003 10.003 0 0 0 12.012 2zm6.09 13.925c-.249.704-1.242 1.3-1.696 1.386-.407.078-.934.14-2.735-.606-2.304-.954-3.791-3.3-3.906-3.453-.115-.152-.936-1.247-.936-2.378 0-1.13.583-1.685.832-1.942.249-.257.54-.321.72-.321.18 0 .36.002.518.01.164.009.387-.063.606.463.224.54.767 1.865.832 1.996.065.13.109.283.022.456-.088.173-.131.283-.262.436-.131.152-.277.34-.395.456-.13.13-.267.272-.115.534.152.261.678 1.116 1.455 1.81.998.89 1.84 1.167 2.102 1.297.262.13.414.109.568-.065.152-.174.656-.763.832-1.02.176-.257.35-.217.59-.13.24.088 1.523.719 1.785.85.262.13.436.196.501.304.065.109.065.63-.184 1.334z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-emerald-800 dark:text-emerald-400">Kirim Rincian Pesanan ke WhatsApp Admin</h4>
                <p class="text-xs text-emerald-700 dark:text-emerald-500 mt-0.5">
                    Pesanan Anda <strong>#{{ $latestOrder->order_number }}</strong> berhasil dibuat! Kirim rincian pesanan ke WhatsApp Admin agar langsung diproses.
                </p>
            </div>
        </div>
        <a href="{{ $latestOrder->getWhatsAppUrl() }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 transition shrink-0">
            Kirim ke WhatsApp
        </a>
    </div>
    @endif
@endif

{{-- Summary Stats --}}
<div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
    @php
        $stats = [
            ['label'=>'Total Order','value'=>$orders->total(),'color'=>'blue'],
            ['label'=>'Menunggu','value'=>$orders->getCollection()->whereIn('status.value',['submitted','sales_reviewed','admin_approved'])->count(),'color'=>'amber'],
        ];
    @endphp
    <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 dark:border-blue-900/30 dark:bg-blue-900/10">
        <p class="text-2xl font-bold text-blue-600">{{ $orders->total() }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Total Order</p>
    </div>
    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 dark:border-amber-900/30 dark:bg-amber-900/10">
        <p class="text-2xl font-bold text-amber-600">Rp {{ number_format($customer->piutang_balance, 0, ',', '.') }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Piutang</p>
    </div>
</div>

{{-- Orders List --}}
<div class="space-y-3">
    @forelse($orders as $order)
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="flex items-center justify-between border-b border-gray-50 px-5 py-3 dark:border-gray-800">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $order->order_number }}</p>
                    <p class="text-xs text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-semibold
                @if($order->status->color() === 'success') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                @elseif($order->status->color() === 'warning') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                @elseif($order->status->color() === 'danger') bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400
                @elseif($order->status->color() === 'info') bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400
                @else bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400
                @endif">
                {{ $order->status->label() }}
            </span>
        </div>
        <div class="px-5 py-3">
            <div class="flex flex-wrap gap-1">
                @foreach($order->items->take(3) as $item)
                <span class="rounded-lg bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                    {{ $item->product->name }} ×{{ $item->quantity }}
                </span>
                @endforeach
                @if($order->items->count() > 3)
                <span class="rounded-lg bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-gray-800">+{{ $order->items->count()-3 }} lagi</span>
                @endif
            </div>
        </div>
        {{-- Shipment / Tracking --}}
        @if($order->salesOrder?->shipment)
        @php $shipment = $order->salesOrder->shipment; @endphp
        <div class="border-t border-gray-50 px-5 py-3 dark:border-gray-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    @if($shipment->courier)
                    <span>{{ $shipment->courier }} · {{ $shipment->tracking_number ?? '—' }}</span>
                    @else
                    <span>Tracking: {{ $shipment->tracking_number ?? '—' }}</span>
                    @endif
                    <span class="mx-1 text-gray-300">|</span>
                    <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium dark:bg-gray-800">
                        @switch($shipment->status)
                            @case('processing') Diproses @break
                            @case('shipped') Dikirim @break
                            @case('customer_confirmed') Menunggu Verifikasi @break
                            @case('delivered') Diterima @break
                            @default {{ $shipment->status }}
                        @endswitch
                    </span>
                </div>
                @if($shipment->canBeConfirmedByCustomer())
                <form method="POST" action="{{ route('portal.shipments.confirm', $shipment->id) }}">
                    @csrf
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 transition"
                            onclick="return confirm('Konfirmasi bahwa barang sudah diterima?')">
                        Saya Sudah Terima
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif

        <div class="flex items-center justify-between border-t border-gray-50 px-5 py-3 dark:border-gray-800">
            <span class="text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            @if($order->status === \App\Enums\OrderStatus::Submitted)
            <a href="{{ $order->getWhatsAppUrl() }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                <svg class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                    <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 0 0 1.37 5.054L2 22l5.132-1.347a9.936 9.936 0 0 0 4.88 1.28c5.508 0 9.99-4.478 9.99-9.984A10.003 10.003 0 0 0 12.012 2zm6.09 13.925c-.249.704-1.242 1.3-1.696 1.386-.407.078-.934.14-2.735-.606-2.304-.954-3.791-3.3-3.906-3.453-.115-.152-.936-1.247-.936-2.378 0-1.13.583-1.685.832-1.942.249-.257.54-.321.72-.321.18 0 .36.002.518.01.164.009.387-.063.606.463.224.54.767 1.865.832 1.996.065.13.109.283.022.456-.088.173-.131.283-.262.436-.131.152-.277.34-.395.456-.13.13-.267.272-.115.534.152.261.678 1.116 1.455 1.81.998.89 1.84 1.167 2.102 1.297.262.13.414.109.568-.065.152-.174.656-.763.832-1.02.176-.257.35-.217.59-.13.24.088 1.523.719 1.785.85.262.13.436.196.501.304.065.109.065.63-.184 1.334z"/>
                </svg>
                Kirim Rincian WA →
            </a>
            @elseif($order->salesOrder)
            <a href="{{ route('portal.invoices.index') }}" class="text-xs font-medium text-blue-600 hover:underline">
                Lihat Invoice →
            </a>
            @endif
        </div>
    </div>
    @empty
    <div class="rounded-2xl border-2 border-dashed border-gray-200 py-16 text-center dark:border-gray-700">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
        <p class="mt-3 text-gray-400">Belum ada order</p>
        <a href="{{ route('portal.orders.create') }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Buat Order Pertama
        </a>
    </div>
    @endforelse
</div>

<div class="mt-6">{{ $orders->links() }}</div>
@endsection
