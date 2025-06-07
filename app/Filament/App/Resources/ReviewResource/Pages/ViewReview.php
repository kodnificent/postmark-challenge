<?php

namespace App\Filament\App\Resources\ReviewResource\Pages;

use App\Filament\App\Resources\ReviewResource;
use App\Filament\App\Resources\ReviewResource\Widgets\RiskMeterWidget;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RiskMeterWidget::class,
        ];
    }
}
