<?php

declare(strict_types=1);

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Draft         = 'draft';
    case Ordered       = 'ordered';
    case GoodsReceived = 'goods_received';
    case QCPassed      = 'qc_passed';
    case Completed     = 'completed';
    case Cancelled     = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft         => 'Draft PO',
            self::Ordered       => 'Dipesan ke Supplier',
            self::GoodsReceived => 'Barang Masuk',
            self::QCPassed      => 'Lolos QC',
            self::Completed     => 'Selesai',
            self::Cancelled     => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft         => 'gray',
            self::Ordered       => 'warning',
            self::GoodsReceived => 'info',
            self::QCPassed      => 'primary',
            self::Completed     => 'success',
            self::Cancelled     => 'danger',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->toArray();
    }
}
