<div
    x-data="realtimeClock()"
    x-init="start()"
    class="fi-header flex flex-col gap-y-2 sm:flex-row sm:items-center sm:justify-between mb-6">

    {{-- Kiri: judul & sambutan --}}
    <div>
        <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
            Dasbor
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
            Selamat datang,
            <span class="font-semibold text-primary-600 dark:text-primary-400">
                {{ auth()->user()?->name }}
            </span>
            &mdash;
            <span x-text="greeting"></span>
        </p>
    </div>

    {{-- Kanan: jam & tanggal --}}
    <div class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-white/10
                bg-white dark:bg-white/5 px-4 py-2.5 shadow-sm self-start sm:self-auto">

        {{-- Jam --}}
        <div class="text-center">
            <div class="font-mono text-xl font-bold leading-none text-gray-900 dark:text-white tracking-widest"
                 x-text="clock"></div>
            <div class="text-[10px] font-semibold text-primary-500 uppercase tracking-wider mt-0.5">WIB</div>
        </div>

        <div class="w-px h-8 bg-gray-200 dark:bg-white/10"></div>

        {{-- Tanggal --}}
        <div class="text-center">
            <div class="text-xs font-semibold text-gray-700 dark:text-gray-200 leading-tight"
                 x-text="dayName"></div>
            <div class="text-xs text-gray-500 dark:text-gray-400 leading-tight mt-0.5"
                 x-text="dateStr"></div>
        </div>
    </div>
</div>

<script>
function realtimeClock() {
    return {
        clock:   '',
        dayName: '',
        dateStr: '',
        greeting: '',
        notifCount: 0,

        start() {
            this.tick();
            setInterval(() => this.tick(), 1000);
            this.listenEvents();
        },

        tick() {
            const now    = new Date();
            const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            const months = ['Jan','Feb','Mar','Apr','Mei','Jun',
                            'Jul','Agu','Sep','Okt','Nov','Des'];

            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const ss = String(now.getSeconds()).padStart(2, '0');

            this.clock   = `${hh}:${mm}:${ss}`;
            this.dayName = days[now.getDay()];
            this.dateStr = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;

            const h = now.getHours();
            if      (h >= 4  && h < 11) this.greeting = 'Selamat pagi!';
            else if (h >= 11 && h < 15) this.greeting = 'Selamat siang!';
            else if (h >= 15 && h < 18) this.greeting = 'Selamat sore!';
            else                        this.greeting = 'Selamat malam!';
        },

        listenEvents() {
            if (typeof window.Echo === 'undefined') return;

            window.Echo.channel('admin')
                .listen('.order.submitted', (e) => {
                    window.toast(`Pesanan Baru: ${e.order_number} dari ${e.customer_name}`, 'success');
                    this.notifCount++;
                })
                .listen('.order.status_changed', (e) => {
                    window.toast(`Pesanan ${e.order_number}: ${e.new_status_label}`, 'info');
                })
                .listen('.payment.uploaded', (e) => {
                    window.toast(`Pembayaran ${e.payment_number} dari ${e.customer_name}`, 'warning');
                    this.notifCount++;
                })
                .listen('.payment.verified', (e) => {
                    window.toast(`Pembayaran ${e.payment_number} terverifikasi`, 'success');
                })
                .listen('.shipment.confirmed', (e) => {
                    window.toast(`Pengiriman ${e.shipment_number} dikonfirmasi customer`, 'info');
                    this.notifCount++;
                });
        },
    };
}
</script>
