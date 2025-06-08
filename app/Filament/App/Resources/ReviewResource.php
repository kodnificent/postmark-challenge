<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ReviewResource\Pages;
use App\Filament\App\Resources\ReviewResource\RelationManagers\ClausesRelationManager;
use App\Filament\App\Resources\ReviewResource\Widgets\ContractEmailWidget;
use App\Jobs\StartReview;
use App\Models\Enums\ReviewStatus;
use App\Models\Review;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Contracts';

    protected static ?string $modelLabel = 'Contract';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('risk_score')
                    ->label('⚠️ Risk Score')
                    ->formatStateUsing(function ($state) {
                        if ($state <= 20) return "✅ {$state}/100";
                        if ($state <= 40) return "🟡 {$state}/100";

                        return "❗️ {$state}/100";
                    }),
                TextColumn::make('title'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('retry')
                    ->visible(fn ($record) => $record->status === ReviewStatus::FAILED)
                    ->action(function ($record) {
                        StartReview::dispatch($record);

                        Notification::make()
                            ->title('Contract Queued for retry')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ClausesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'view' => Pages\ViewReview::route('/{record}')
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Grid::make(2)->schema([
                TextEntry::make('summary')->hiddenLabel(),
            ]),
            Grid::make(2)->schema([
                TextEntry::make('risk_score_comment')->label('Comment'),
            ]),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', request()->user()->id);
    }

    public static function getWidgets(): array
    {
        return [
            ContractEmailWidget::class,
        ];
    }
}
