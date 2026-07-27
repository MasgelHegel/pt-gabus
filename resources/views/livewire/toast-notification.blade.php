<div aria-live="assertive"
     class="pointer-events-none fixed inset-0 z-50 flex flex-col items-end justify-end gap-3 p-4 sm:items-end sm:justify-end sm:p-6">

    @foreach($toasts as $toast)
        <div wire:key="toast-{{ $toast['id'] }}"
             x-data="{ show: false }"
             x-init="
                 requestAnimationFrame(() => show = true);
                 setTimeout(() => {
                     show = false;
                     setTimeout(() => $wire.dismiss({{ $toast['id'] }}), 300);
                 }, {{ $toast['duration'] }})
             "
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4"
             x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-2xl shadow-lg ring-1 ring-black/5
                @if($toast['type'] === 'success') bg-white dark:bg-gray-900 ring-emerald-500/20
                @elseif($toast['type'] === 'error') bg-white dark:bg-gray-900 ring-rose-500/20
                @elseif($toast['type'] === 'warning') bg-white dark:bg-gray-900 ring-amber-500/20
                @else bg-white dark:bg-gray-900 ring-blue-500/20
                @endif">
            <div class="flex items-start gap-3 p-4">
                {{-- Icon --}}
                <div class="mt-0.5 flex-shrink-0">
                    @if($toast['type'] === 'success')
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                        </div>
                    @elseif($toast['type'] === 'error')
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/30">
                            <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    @elseif($toast['type'] === 'warning')
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                            <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126Z"/>
                            </svg>
                        </div>
                    @else
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                            <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $toast['message'] }}
                    </p>
                </div>

                {{-- Dismiss --}}
                <button wire:click="dismiss({{ $toast['id'] }})"
                        class="flex-shrink-0 rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endforeach
</div>
