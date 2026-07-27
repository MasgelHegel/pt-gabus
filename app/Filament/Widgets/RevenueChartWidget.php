<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading      = 'Revenue vs Order';
    protected ?string $description  = '6 bulan terakhir';
    protected ?string $maxHeight    = '300px';
    protected static ?int $sort     = 3;
    protected ?string $pollingInterval = '30s';

    public function getColumnSpan(): int|string|array
    {
        return auth()->user()?->isSuperAdmin() ? 1 : 'full';
    }

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(function (int $ago) {
            $date  = now()->subMonths($ago);
            $label = $date->translatedFormat('M Y');

            $revenue = (float) Invoice::where('status', InvoiceStatus::Paid->value)
                ->whereYear('updated_at', $date->year)
                ->whereMonth('updated_at', $date->month)
                ->sum('total_amount');

            $orders = Order::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            return compact('label', 'revenue', 'orders');
        });

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (Rp)',
                    'data'            => $months->pluck('revenue')->toArray(),
                    'backgroundColor' => 'rgba(59,130,246,0.1)',
                    'borderColor'     => 'rgba(59,130,246,1)',
                    'borderWidth'     => 2,
                    'fill'            => true,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Jumlah Order',
                    'data'            => $months->pluck('orders')->toArray(),
                    'backgroundColor' => 'rgba(99,102,241,0.1)',
                    'borderColor'     => 'rgba(99,102,241,1)',
                    'borderWidth'     => 2,
                    'fill'            => false,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y1',
                ],
            ],
            'labels' => $months->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => true]],
            'scales' => [
                'y'  => ['type' => 'linear', 'position' => 'left',  'beginAtZero' => true],
                'y1' => ['type' => 'linear', 'position' => 'right', 'beginAtZero' => true,
                         'grid' => ['drawOnChartArea' => false]],
            ],
        ];
    }
}
