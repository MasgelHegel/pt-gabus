<div x-data="{ open: @entangle('isOpen') }"
     @keydown.window.escape="$wire.close()"
     @keydown.window.cmd.k.prevent="$wire.open()"
     @keydown.window.ctrl.k.prevent="$wire.open()">

    {{-- Trigger button --}}
    <button @click="$wire.open()"
            class="flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm text-gray-500 shadow-sm backdrop-blur-sm transition hover:border-blue-300 hover:bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-blue-500">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z" />
        </svg>
        <span>Cari...</span>
        <kbd class="ml-auto hidden rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-400 dark:bg-gray-700 dark:text-gray-500 sm:inline">⌘K</kbd>
    </button>

    {{-- Modal overlay --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-20"
         style="display: none;">

        <div @click="$wire.close()" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="relative mx-auto max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">

            {{-- Search input --}}
            <div class="flex items-center gap-3 border-b border-gray-100 px-4 dark:border-gray-800">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z"/>
                </svg>
                <input wire:model.live.debounce.300ms="query"
                       type="text"
                       placeholder="Cari pengguna, menu, fitur..."
                       class="flex-1 border-0 bg-transparent py-4 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 dark:text-white"
                       x-ref="searchInput"
                       x-init="$nextTick(() => { if (open) $refs.searchInput.focus() })"
                       @keydown.escape="$wire.close()">
                <button @click="$wire.close()" class="rounded-lg px-2 py-1 text-xs text-gray-400 hover:text-gray-600">ESC</button>
            </div>

            {{-- Results --}}
            @if(strlen($query) >= 2)
                <div class="max-h-80 overflow-y-auto p-2">
                    @forelse($this->results as $group)
                        @if($group['results']->isNotEmpty())
                            <div class="mb-2">
                                <p class="px-3 py-1 text-xs font-semibold uppercase tracking-wider text-gray-400">
                                    {{ $group['label'] }}
                                </p>
                                @foreach($group['results'] as $result)
                                    <a href="{{ $result['url'] }}"
                                       @click="$wire.close()"
                                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $result['title'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $result['subtitle'] }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @empty
                        <div class="py-8 text-center">
                            <p class="text-sm text-gray-500">Tidak ada hasil untuk "<strong>{{ $query }}</strong>"</p>
                        </div>
                    @endforelse
                </div>
            @else
                <div class="py-8 text-center">
                    <p class="text-sm text-gray-400">Ketik minimal 2 karakter untuk mencari</p>
                </div>
            @endif
        </div>
    </div>
</div>
