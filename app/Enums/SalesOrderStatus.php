<?php

declare(strict_types=1);

namespace App\Enums;

enum SalesOrderStatus: string
{
    case Draft       = 'draft';
    case Processing  = 'processing';
    case ReadyToShip = 'ready_to_ship';
    case Shipped     = 'shipped';
    case Delivered   = 'delivered';
    case Completed   = 'completed';
    case Cancelled   = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft       => 'Draft SO',
            self::Processing  => 'Diproses',
            self::ReadyToShip => 'Siap Dikirim',
            self::Shipped     => 'Dikirim',
            self::Delivered   => 'Diterima Customer',
            self::Completed   => 'Selesai',
            self::Cancelled   => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft       => 'gray',
            self::Processing  => 'warning',
            self::ReadyToShip => 'info',
            self::Shipped     => 'primary',
            self::Delivered   => 'success',
            self::Completed   => 'success',
            self::Cancelled   => 'danger',
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
