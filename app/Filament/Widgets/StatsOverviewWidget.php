<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\UserStatus;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';
    protected ?string $pollingInterval = '15s';

    public static function canView(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    protected function getStats(): array
    {
        $totalUsers   = User::count();
        $activeUsers  = User::where('status', UserStatus::Active)->count();
        $newThisMonth = User::whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)
                            ->count();
        $loggedInToday = User::whereDate('last_login_at', today())->count();

        return [
            Stat::make('Total Pengguna', number_format($totalUsers))
                ->description('Semua pengguna terdaftar')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, $totalUsers]),

            Stat::make('Pengguna Aktif', number_format($activeUsers))
                ->description(number_format(($totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0), 1) . '% dari total')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart([3, 5, 2, 8, 4, 6, 3, $activeUsers]),

            Stat::make('Baru Bulan Ini', number_format($newThisMonth))
                ->description('Pengguna baru ' . now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-o-user-plus')
                ->color('info')
                ->chart([1, 2, 1, 3, 2, 4, 2, $newThisMonth]),

            Stat::make('Login Hari Ini', number_format($loggedInToday))
                ->description('Pengguna aktif hari ini')
                ->descriptionIcon('heroicon-o-arrow-right-end-on-rectangle')
                ->color('warning')
                ->chart([2, 4, 3, 6, 5, 3, 4, $loggedInToday]),
        ];
    }
}
