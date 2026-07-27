<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Widgets\ErpStatsWidget;
use App\Filament\Widgets\LowStockAlertWidget;
use App\Filament\Widgets\PendingPaymentsWidget;
use App\Filament\Widgets\RevenueChartWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession as SessionAuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->darkMode(true)
            ->colors([
                'primary' => Color::Blue,
                'gray'    => Color::Slate,
                'info'    => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger'  => Color::Rose,
            ])
            ->font('Inter')
            ->brandName(config('app.name', 'Gabus ERP'))
            ->favicon(asset('favicon.ico'))
            ->navigationGroups([
                NavigationGroup::make('Master Data'),
                NavigationGroup::make('Operasional Sales'),
                NavigationGroup::make('Procurement & Logistik'),
                NavigationGroup::make('Keuangan & Kas'),
                NavigationGroup::make('Laporan'),
                NavigationGroup::make('SDM'),
                NavigationGroup::make('Pengaturan System'),
            ])
            ->discoverResources(
                in: app_path('Filament/Resources'),
                for: 'App\\Filament\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\\Filament\\Pages'
            )
            ->discoverWidgets(
                in: app_path('Filament/Widgets'),
                for: 'App\\Filament\\Widgets'
            )
            ->widgets([
                AccountWidget::class,
                ErpStatsWidget::class,
                RevenueChartWidget::class,
                PendingPaymentsWidget::class,
                LowStockAlertWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                \App\Http\Middleware\FilamentAdminAuthenticate::class,
            ]);
    }
}
