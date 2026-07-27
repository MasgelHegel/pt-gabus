<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending  = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Pending  => 'Menunggu Verifikasi',
            self::Verified => 'Terverifikasi (Lunas)',
            self::Rejected => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending  => 'warning',
            self::Verified => 'success',
            self::Rejected => 'danger',
        };
    }

    /** @return array<string,string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->toArray();
    }
}
