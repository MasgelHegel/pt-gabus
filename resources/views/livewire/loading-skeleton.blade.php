<div class="animate-pulse">
    @if($type === 'table')
        {{-- Table skeleton --}}
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="h-5 w-48 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @for($i = 0; $i < $rows; $i++)
                    <div class="flex items-center gap-4 px-6 py-4">
                        <div class="h-9 w-9 rounded-full bg-gray-200 dark:bg-gray-700"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-4 w-2/5 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
                            <div class="h-3 w-3/5 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                        </div>
                        <div class="h-6 w-16 rounded-full bg-gray-100 dark:bg-gray-800"></div>
                        <div class="h-6 w-20 rounded-full bg-gray-100 dark:bg-gray-800"></div>
                    </div>
                @endfor
            </div>
        </div>

    @elseif($type === 'card')
        {{-- Card grid skeleton --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @for($i = 0; $i < $rows; $i++)
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-gray-200 dark:bg-gray-700"></div>
                        <div class="space-y-2">
                            <div class="h-4 w-28 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
                            <div class="h-3 w-20 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="h-3 w-full rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                        <div class="h-3 w-4/5 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                        <div class="h-3 w-3/5 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                    </div>
                </div>
            @endfor
        </div>

    @else
        {{-- List skeleton --}}
        <div class="space-y-3">
            @for($i = 0; $i < $rows; $i++)
                <div class="flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm dark:bg-gray-900">
                    <div class="h-10 w-10 rounded-xl bg-gray-200 dark:bg-gray-700"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 w-1/3 rounded-lg bg-gray-200 dark:bg-gray-700"></div>
                        <div class="h-3 w-1/2 rounded-lg bg-gray-100 dark:bg-gray-800"></div>
                    </div>
                </div>
            @endfor
        </div>
    @endif
</div>
