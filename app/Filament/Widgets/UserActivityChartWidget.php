<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class UserActivityChartWidget extends ChartWidget
{
    protected ?string $heading = 'Pendaftaran Pengguna';
    protected ?string $description = 'Jumlah pengguna baru per bulan';
    protected ?string $maxHeight = '300px';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    protected function getData(): array
    {
        $data = collect(range(11, 0))->map(function (int $monthsAgo): array {
            $date  = now()->subMonths($monthsAgo);
            $count = User::whereYear('created_at', $date->year)
                         ->whereMonth('created_at', $date->month)
                         ->count();

            return [
                'month' => $date->translatedFormat('M Y'),
                'count' => $count,
            ];
        });

        return [
            'datasets' => [
                [
                    'label'           => 'Pengguna Baru',
                    'data'            => $data->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor'     => 'rgba(59, 130, 246, 1)',
                    'borderWidth'     => 2,
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $data->pluck('month')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => ['stepSize' => 1],
                ],
            ],
        ];
    }
}
