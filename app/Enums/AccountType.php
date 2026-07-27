<?php

declare(strict_types=1);

namespace App\Enums;

enum AccountType: string
{
    case Asset     = 'asset';
    case Liability = 'liability';
    case Equity    = 'equity';
    case Revenue   = 'revenue';
    case Expense   = 'expense';

    public function label(): string
    {
        return match($this) {
            self::Asset     => 'Aset / Aktiva',
            self::Liability => 'Kewajiban / Pasiva',
            self::Equity    => 'Ekuitas / Modal',
            self::Revenue   => 'Pendapatan / Penjualan',
            self::Expense   => 'Beban / Biaya',
        };
    }
}
