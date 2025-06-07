<?php

namespace App\Filament\App\Resources\ReviewResource\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\HtmlString;

class RiskMeterWidget extends ChartWidget
{
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $riskScore = 80; // Replace with actual dynamic value if needed

        return [
            'labels' => ['Risk', 'Remaining'],
            'datasets' => [
                [
                    'data' => [$riskScore, 100 - $riskScore],
                    'backgroundColor' => [
                        $riskScore <= 20 ? '#22c55e' : ($riskScore <= 40 ? '#facc15' : '#ef4444'),
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
        $riskScore = 80; // Match the value from getData()

        return [
            'cutout' => '80%',
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => ['enabled' => false],
            ],
            'scales' => [
                'x' => ['display' => false],
                'y' => ['display' => false],
            ],
            'animation' => [
                'onComplete' => new HtmlString("
                    function () {
                    console.log('complete')
                        const chart = this.chart;
                        const ctx = chart.ctx;
                        const width = chart.width;
                        const height = chart.height;
                        ctx.save();
                        ctx.font = 'bold 24px sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillStyle = '#111827';
                        ctx.fillText('$riskScore', width / 2, height / 2);
                        ctx.restore();
                    }
                "),
            ],
        ];
    }

    protected function getMaxHeight(): ?string
    {
        return '200px';
    }
}
