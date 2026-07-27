<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm dark:bg-gray-900 dark:border dark:border-gray-800">
            <div>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->getTitle() }}</h2>
                <p class="text-sm text-gray-500">{{ $this->getTable()->getHeading() }}</p>
            </div>
        </div>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
