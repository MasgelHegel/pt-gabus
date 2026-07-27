@extends('portal.layout')
@section('title','Invoice Saya')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Invoice</h1>
    <p class="text-sm text-gray-500">Daftar tagihan dan upload bukti pembayaran</p>
</div>

{{-- Piutang Summary --}}
<div class="mb-6 overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 p-5 text-white shadow-lg">
    <p class="text-sm text-indigo-100">Total Piutang Anda</p>
    <p class="mt-1 text-3xl font-bold">Rp {{ number_format($customer->piutang_balance, 0, ',', '.') }}</p>
    <p class="mt-0.5 text-sm text-indigo-200">Limit kredit: Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}</p>
</div>

<div class="space-y-4">
    @forelse($invoices as $invoice)
    <div x-data="{ open: false }" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        {{-- Invoice Header --}}
        <button @click="open = !open" class="flex w-full items-center justify-between px-5 py-4 text-left">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl
                    @if($invoice->status->color() === 'success') bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30
                    @elseif($invoice->status->color() === 'warning') bg-amber-100 text-amber-600 dark:bg-amber-900/30
                    @elseif($invoice->status->color() === 'danger') bg-rose-100 text-rose-600 dark:bg-rose-900/30
                    @elseif($invoice->status->color() === 'info') bg-sky-100 text-sky-600 dark:bg-sky-900/30
                    @else bg-gray-100 text-gray-600 dark:bg-gray-800 @endif">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</p>
                    <p class="text-xs text-gray-500">Tgl: {{ $invoice->invoice_date->format('d M Y') }} · Jatuh tempo: <span class="{{ $invoice->due_date->isPast() && $invoice->status->value !== 'paid' ? 'font-semibold text-rose-500' : '' }}">{{ $invoice->due_date->format('d M Y') }}</span></p>
                </div>
            </div>
            <div class="flex flex-col items-end gap-1">
                <span class="rounded-full px-3 py-0.5 text-xs font-semibold
                    @if($invoice->status->color() === 'success') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                    @elseif($invoice->status->color() === 'warning') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
                    @elseif($invoice->status->color() === 'danger') bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400
                    @elseif($invoice->status->color() === 'info') bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400
                    @else bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 @endif">
                    {{ $invoice->status->label() }}
                </span>
                <span class="text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
            </div>
        </button>

        {{-- Invoice Detail --}}
        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             class="border-t border-gray-100 dark:border-gray-800">
            {{-- Items --}}
            <div class="px-5 py-3">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Item</p>
                <div class="space-y-1.5">
                    @foreach($invoice->items as $item)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-700 dark:text-gray-300">{{ $item->product->name }} × {{ $item->quantity }}</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-3 space-y-1 border-t border-gray-100 pt-2 dark:border-gray-800">
                    <div class="flex justify-between text-sm font-bold"><span>Total</span><span class="text-blue-600">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span></div>
                </div>
            </div>

            {{-- Payment History --}}
            @if($invoice->payments->count())
            <div class="border-t border-gray-100 px-5 py-3 dark:border-gray-800">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Riwayat Pembayaran</p>
                @foreach($invoice->payments as $payment)
                <div class="flex items-center justify-between rounded-xl bg-gray-50 px-3 py-2 dark:bg-gray-800">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $payment->payment_number }}</p>
                        <p class="text-xs text-gray-500">{{ $payment->payment_date->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                        <span class="text-xs {{ $payment->status->value === 'verified' ? 'text-emerald-600' : ($payment->status->value === 'rejected' ? 'text-rose-500' : 'text-amber-600') }}">{{ $payment->status->label() }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Upload Payment --}}
            @if(in_array($invoice->status->value, ['unpaid', 'overdue']))
            <div x-data="{ formOpen: false }" class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                <button @click="formOpen = !formOpen" class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                    Upload Bukti Pembayaran
                </button>
                <div x-show="formOpen" x-cloak class="mt-3">
                    <form method="POST" action="{{ route('portal.invoices.upload-payment', $invoice->id) }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Jumlah Pembayaran</label>
                            <input type="number" name="amount" value="{{ $invoice->total_amount }}" step="0.01" min="1" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Tanggal Bayar</label>
                            <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Bukti Transfer (JPG/PNG/PDF, max 5MB)</label>
                            <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" required class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                            Kirim Bukti Bayar
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="rounded-2xl border-2 border-dashed border-gray-200 py-16 text-center dark:border-gray-700">
        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
        <p class="mt-3 text-gray-400">Belum ada invoice</p>
    </div>
    @endforelse
</div>

<div class="mt-6">{{ $invoices->links() }}</div>
@endsection
