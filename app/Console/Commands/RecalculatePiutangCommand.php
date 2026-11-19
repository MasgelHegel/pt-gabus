<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculatePiutangCommand extends Command
{
    protected $signature = 'piutang:recalculate {--dry-run : Tampilkan hasil tanpa menyimpan}';

    protected $description = 'Hitung ulang saldo piutang semua customer dari invoice dan pembayaran terverifikasi, dan koreksi saldo minus';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $customers = Customer::query()->get(['id', 'name', 'piutang_balance']);

        $fixed = 0;

        foreach ($customers as $customer) {
            $invoiceTotal = (float) DB::table('invoices')
                ->where('customer_id', $customer->id)
                ->whereNot('status', InvoiceStatus::Cancelled->value)
                ->sum('total_amount');

            $paidTotal = (float) DB::table('payments')
                ->where('customer_id', $customer->id)
                ->where('status', PaymentStatus::Verified->value)
                ->sum('amount');

            $correct = max(0.0, round($invoiceTotal - $paidTotal, 2));
            $current = (float) $customer->piutang_balance;

            if (abs($current - $correct) < 0.01) {
                continue;
            }

            $this->line(sprintf(
                '%s: %.2f -> %.2f (invoice %.2f - bayar %.2f)',
                $customer->name,
                $current,
                $correct,
                $invoiceTotal,
                $paidTotal
            ));

            if (! $dryRun) {
                Customer::where('id', $customer->id)
                    ->update(['piutang_balance' => $correct]);
            }

            $fixed++;
        }

        $this->info($dryRun
            ? "Dry run: {$fixed} customer perlu dikoreksi. Jalankan tanpa --dry-run untuk menyimpan."
            : "Selesai: {$fixed} customer dikoreksi.");

        return self::SUCCESS;
    }
}
