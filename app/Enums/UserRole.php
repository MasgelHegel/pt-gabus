<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin      = 'admin';
    case Sales      = 'sales';
    case Customer   = 'customer';
    // Legacy / extra
    case Manager    = 'manager';
    case Staff      = 'staff';
    case Cashier    = 'cashier';
    case Viewer     = 'viewer';

    public function label(): string
    {
        return match($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin      => 'Admin',
            self::Sales      => 'Sales',
            self::Customer   => 'Customer',
            self::Manager    => 'Manager',
            self::Staff      => 'Staff',
            self::Cashier    => 'Kasir',
            self::Viewer     => 'Viewer',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::SuperAdmin => 'danger',
            self::Admin      => 'warning',
            self::Sales      => 'info',
            self::Customer   => 'success',
            self::Manager    => 'primary',
            self::Staff      => 'gray',
            self::Cashier    => 'success',
            self::Viewer     => 'gray',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->pluck('name', 'value')
            ->map(fn ($name, $value) => self::from($value)->label())
            ->toArray();
    }

    public function isPortalRole(): bool
    {
        return $this === self::Customer;
    }

    public function isAdminRole(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::Sales, self::Manager, self::Staff], true);
    }
}
