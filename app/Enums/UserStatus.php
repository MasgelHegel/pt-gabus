<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Banned   = 'banned';
    case Pending  = 'pending';

    public function label(): string
    {
        return match($this) {
            self::Active   => 'Aktif',
            self::Inactive => 'Tidak Aktif',
            self::Banned   => 'Diblokir',
            self::Pending  => 'Menunggu Verifikasi',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active   => 'success',
            self::Inactive => 'gray',
            self::Banned   => 'danger',
            self::Pending  => 'warning',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Active   => 'heroicon-o-check-circle',
            self::Inactive => 'heroicon-o-minus-circle',
            self::Banned   => 'heroicon-o-x-circle',
            self::Pending  => 'heroicon-o-clock',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_column(
            array_map(fn (self $case) => ['value' => $case->value, 'label' => $case->label()], self::cases()),
            'label',
            'value'
        );
    }
}
