@php
    $record     = $getRecord();
    $proofFile  = $record?->proof_file;
    $isPdf      = $proofFile && str_ends_with(strtolower($proofFile), '.pdf');
    $url        = $proofFile ? asset('storage/' . $proofFile) : null;
@endphp

<div class="w-full">
    @if ($proofFile && ! $isPdf)
        {{-- Gambar bukti transfer --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <img src="{{ $url }}"
                 alt="Bukti Transfer"
                 class="w-full object-contain"
                 style="max-height: 420px;"
                 onerror="this.parentElement.innerHTML = '<div class=\'p-8 text-center text-gray-400 text-sm\'>Gambar tidak dapat dimuat</div>'">
        </div>
        <a href="{{ $url }}"
           target="_blank"
           class="mt-2 flex items-center justify-center gap-1.5 w-full rounded-lg border border-gray-200 dark:border-gray-700 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
            </svg>
            Buka gambar ukuran penuh
        </a>

    @elseif ($proofFile && $isPdf)
        {{-- PDF bukti --}}
        <div class="flex flex-col items-center gap-3 p-8 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-center">
            <svg class="w-14 h-14 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ basename($proofFile) }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Dokumen PDF</p>
            </div>
            <a href="{{ $url }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-100 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Buka / Download PDF
            </a>
        </div>

    @else
        {{-- Tidak ada bukti --}}
        <div class="flex flex-col items-center gap-2 p-8 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 text-center">
            <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
            </svg>
            <p class="text-sm text-gray-400">Belum ada bukti yang diupload</p>
        </div>
    @endif
</div>
