<?php

namespace App\Filament\App\Resources\ReviewResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\HtmlString;

class RiskMeterWidget extends ChartWidget
{
    protected static ?string $pollingInterval = null;

    public int $score = 0;

    protected function getData(): array
    {
        $score = $this->score ?? 0;

        return [
            'labels' => ['Risk', 'Remaining'],
            'datasets' => [
                [
                    'data' => [$score, 100 - $score],
                    'backgroundColor' => [
                        $score <= 20 ? '#22c55e' : ($score <= 40 ? '#facc15' : '#ef4444'),
                        '#e5e7eb',
                    ],
                    'borderWidth' => 0,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        $score = $this->score ?? 0;

        return [
            'cutout' => '80%',
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => ['enabled' => false],
                'textCenter' => [
                    'text' => $score . '%'
                ],
            ],
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
        ];
    }

    protected function getMaxHeight(): ?string
    {
        return '200px';
    }
}
