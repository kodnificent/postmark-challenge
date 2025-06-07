<?php

namespace App\Filament\App\Resources\ReviewResource\Pages;

use App\Filament\App\Resources\ReviewResource;
use App\Filament\App\Resources\ReviewResource\Widgets\RiskMeterWidget;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }

    public function getTitle(): string | Htmlable
    {
        return $this->record->title;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RiskMeterWidget::make(['score' => $this->record->risk_score]),
        ];
    }
}
