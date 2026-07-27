@php
    $proofFile = $record->proof_file;
    $isPdf     = $proofFile && str_ends_with(strtolower($proofFile), '.pdf');
    $url       = $proofFile ? asset('storage/' . $proofFile) : null;
@endphp

<div class="space-y-4 p-1">

    {{-- Info ringkas --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 text-sm">
        <div class="rounded-xl bg-gray-50 dark:bg-white/5 px-3 py-2.5">
            <p class="text-xs text-gray-400 mb-0.5">Customer</p>
            <p class="font-semibold text-gray-900 dark:text-white truncate">
                {{ $record->customer?->name ?? '—' }}
            </p>
        </div>
        <div class="rounded-xl bg-gray-50 dark:bg-white/5 px-3 py-2.5">
            <p class="text-xs text-gray-400 mb-0.5">Invoice</p>
            <p class="font-semibold text-gray-900 dark:text-white truncate">
                {{ $record->invoice?->invoice_number ?? '—' }}
            </p>
        </div>
        <div class="rounded-xl bg-gray-50 dark:bg-white/5 px-3 py-2.5">
            <p class="text-xs text-gray-400 mb-0.5">Nominal</p>
            <p class="font-semibold text-emerald-600 dark:text-emerald-400">
                Rp {{ number_format($record->amount, 0, ',', '.') }}
            </p>
        </div>
        <div class="rounded-xl bg-gray-50 dark:bg-white/5 px-3 py-2.5">
            <p class="text-xs text-gray-400 mb-0.5">Tanggal</p>
            <p class="font-semibold text-gray-900 dark:text-white">
                {{ \Carbon\Carbon::parse($record->payment_date)->translatedFormat('d M Y') }}
            </p>
        </div>
    </div>

    {{-- Bukti --}}
    @if ($proofFile && ! $isPdf)
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
            <img src="{{ $url }}"
                 alt="Bukti Transfer"
                 class="w-full object-contain"
                 style="max-height: 500px;"
                 onerror="this.closest('div').innerHTML='<div class=\'p-10 text-center text-sm text-gray-400\'>Gambar tidak dapat dimuat</div>'">
        </div>
        <a href="{{ $url }}"
           target="_blank"
           class="flex items-center justify-center gap-2 w-full rounded-xl border border-gray-200
                  dark:border-white/10 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-300
                  hover:bg-gray-50 dark:hover:bg-white/5 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5
                         A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
            </svg>
            Buka gambar ukuran penuh di tab baru
        </a>

    @elseif ($proofFile && $isPdf)
        <div class="flex flex-col items-center gap-4 py-10 rounded-xl border border-gray-200
                    dark:border-white/10 bg-gray-50 dark:bg-white/5 text-center">
            <svg class="w-16 h-16 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5
                         A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25
                         m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125
                         h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
            </svg>
            <div>
                <p class="font-semibold text-gray-700 dark:text-gray-200">{{ basename($proofFile) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Dokumen PDF</p>
            </div>
            <a href="{{ $url }}" target="_blank"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-50 dark:bg-red-900/20
                      border border-red-200 dark:border-red-700 text-sm font-semibold text-red-600
                      dark:text-red-400 hover:bg-red-100 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75
                             V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Buka / Download PDF
            </a>
        </div>

    @else
        <div class="flex flex-col items-center gap-2 py-12 rounded-xl border-2 border-dashed
                    border-gray-200 dark:border-white/10 text-center">
            <svg class="w-12 h-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159
                         m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909
                         m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5
                         H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"/>
            </svg>
            <p class="text-sm text-gray-400">Belum ada bukti yang diupload</p>
        </div>
    @endif

</div>
