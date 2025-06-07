<?php

namespace App\Filament\App\Resources\ReviewResource\Pages;

use App\Filament\App\Resources\ReviewResource;
use App\Jobs\StartReview;
use App\Models\Enums\ReviewSource;
use App\Models\Enums\ReviewStatus;
use App\Models\Review;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\PdfToText\Pdf;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Upload Contract')
                ->form([
                    FileUpload::make('file')
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data) {
                    /** @var TemporaryUploadedFile */
                    $file = $data['file'];
                    $path = $file->getRealPath();
                    $text = Pdf::getText($path);

                    $review = new Review();
                    $review->content = $text;
                    $review->source = ReviewSource::APP;
                    $review->status = ReviewStatus::PENDING;
                    $review->user_id = request()->user()->id;
                    $review->save();
                    $file->delete();

                    StartReview::dispatch($review);
                }),
        ];
    }
}
