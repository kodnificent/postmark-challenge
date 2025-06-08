<?php

namespace App\Filament\App\Resources\ReviewResource\Pages;

use App\Filament\App\Resources\ReviewResource;
use App\Filament\App\Resources\ReviewResource\Widgets\ContractEmailWidget;
use App\Jobs\StartReview;
use App\Models\Enums\ReviewSource;
use App\Models\Enums\ReviewStatus;
use App\Models\Review;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\PdfToText\Pdf;
use Webbingbrasil\FilamentCopyActions\Pages\Actions\CopyAction;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ContractEmailWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CopyAction::make('copy_email_address')
                ->copyable(static function (): string {
                    $username = request()->user()->username;
                    $email_domain = config('app.email_forwading_domain');

                    return "{$username}@{$email_domain}";
                }),
            Actions\Action::make('Upload Contract')
                ->color('success')
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
