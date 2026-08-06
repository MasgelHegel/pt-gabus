<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // AKTIVA
            ['code' => '1100', 'name' => 'Kas',               'type' => 'asset', 'is_cash_bank' => true],
            ['code' => '1110', 'name' => 'Bank BCA',           'type' => 'asset', 'is_cash_bank' => true],
            ['code' => '1111', 'name' => 'Bank Mandiri',       'type' => 'asset', 'is_cash_bank' => true],
            ['code' => '1112', 'name' => 'Bank BRI',           'type' => 'asset', 'is_cash_bank' => true],
            ['code' => '1120', 'name' => 'Piutang Usaha',      'type' => 'asset', 'is_cash_bank' => false],
            ['code' => '1200', 'name' => 'Persediaan Barang',  'type' => 'asset', 'is_cash_bank' => false],

            // KEWAJIBAN
            ['code' => '2100', 'name' => 'Hutang Usaha',       'type' => 'liability', 'is_cash_bank' => false],
            ['code' => '2200', 'name' => 'Hutang PPN',         'type' => 'liability', 'is_cash_bank' => false],

            // EKUITAS
            ['code' => '3100', 'name' => 'Modal Pemilik',      'type' => 'equity',    'is_cash_bank' => false],
            ['code' => '3200', 'name' => 'Laba Ditahan',       'type' => 'equity',    'is_cash_bank' => false],

            // PENDAPATAN
            ['code' => '4100', 'name' => 'Pendapatan Penjualan', 'type' => 'revenue', 'is_cash_bank' => false],

            // BEBAN
            ['code' => '5100', 'name' => 'Harga Pokok Penjualan', 'type' => 'expense', 'is_cash_bank' => false],
            ['code' => '5200', 'name' => 'Beban Operasional',      'type' => 'expense', 'is_cash_bank' => false],
        ];

        foreach ($accounts as $acc) {
            Account::firstOrCreate(
                ['code' => $acc['code']],
                $acc
            );
        }

        $this->command->info('Chart of accounts seeded: ' . count($accounts) . ' accounts.');
    }
}
