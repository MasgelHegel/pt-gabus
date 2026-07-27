<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Company Profile Section --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-3 border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <x-heroicon-o-building-office-2 class="h-5 w-5 text-primary-500" />
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Data Perusahaan</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Informasi perusahaan yang tampil di invoice dan dokumen resmi</p>
                </div>
            </div>
            <div class="p-6">
                <form wire:submit="saveCompany">
                    {{ $this->companyForm }}
                    <div class="mt-6 flex justify-end">
                        <x-filament::button
                            type="submit"
                            icon="heroicon-o-check"
                            color="primary"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="saveCompany">Simpan Data Perusahaan</span>
                            <span wire:loading wire:target="saveCompany">Menyimpan...</span>
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>

        {{-- App Settings Section --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-3 border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-success-500" />
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Pengaturan Aplikasi</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Konfigurasi operasional dan preferensi sistem</p>
                </div>
            </div>
            <div class="p-6">
                <form wire:submit="saveSettings">
                    {{ $this->settingsForm }}
                    <div class="mt-6 flex justify-end">
                        <x-filament::button
                            type="submit"
                            icon="heroicon-o-check"
                            color="success"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="saveSettings">Simpan Pengaturan</span>
                            <span wire:loading wire:target="saveSettings">Menyimpan...</span>
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-filament-panels::page>
