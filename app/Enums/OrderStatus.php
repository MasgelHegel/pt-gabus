<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Draft          = 'draft';
    case Submitted      = 'submitted';
    case SalesReviewed  = 'sales_reviewed';
    case AdminApproved  = 'admin_approved';
    case SOCreated      = 'so_created';
    case Rejected       = 'rejected';
    case Cancelled      = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft         => 'Draft',
            self::Submitted     => 'Menunggu Sales',
            self::SalesReviewed => 'Ditinjau Sales',
            self::AdminApproved => 'Disetujui Admin',
            self::SOCreated     => 'Sales Order Dibuat',
            self::Rejected      => 'Ditolak',
            self::Cancelled     => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft         => 'gray',
            self::Submitted     => 'warning',
            self::SalesReviewed => 'info',
            self::AdminApproved => 'primary',
            self::SOCreated     => 'success',
            self::Rejected      => 'danger',
            self::Cancelled     => 'danger',
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
