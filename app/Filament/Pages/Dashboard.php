<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-home';
    }

    public static function getNavigationLabel(): string
    {
        return 'Dasbor';
    }

    public static function getNavigationSort(): ?int
    {
        return 0;
    }

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.pages.dashboard-header');
    }
}
