<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatus: string
{
    case Unpaid          = 'unpaid';
    case PaymentUploaded = 'payment_uploaded';
    case Paid            = 'paid';
    case Overdue         = 'overdue';
    case Cancelled       = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Unpaid          => 'Belum Dibayar',
            self::PaymentUploaded => 'Bukti Bayar Diupload',
            self::Paid            => 'Lunas',
            self::Overdue         => 'Jatuh Tempo',
            self::Cancelled       => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Unpaid          => 'warning',
            self::PaymentUploaded => 'info',
            self::Paid            => 'success',
            self::Overdue         => 'danger',
            self::Cancelled       => 'gray',
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
