<?php

namespace App\Filament\App\Resources\ReviewResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ClausesRelationManager extends RelationManager
{
    protected static string $relationship = 'clauses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Grid::make(1)
                ->schema([
                    Infolists\Components\TextEntry::make('risk_score')
                        ->label('⚠️ Risk Score')
                        ->formatStateUsing(function ($state) {
                            if ($state <= 20) return "✅ {$state}/100";
                            if ($state <= 40) return "🟡 {$state}/100";

                            return "❗️ {$state}/100";
                        }),
                    Infolists\Components\TextEntry::make('title')->label('Clause Title'),
                    Infolists\Components\TextEntry::make('comment')->label('Summary'),
                    Infolists\Components\TextEntry::make('risk_score_comment')->label('Comment')
                ]),
        ])->inlineLabel();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Clause title'),
                Tables\Columns\TextColumn::make('risk_score')
                    ->label('⚠️ Risk Score')
                    ->formatStateUsing(function ($state) {
                        if ($state <= 20) return "✅ {$state}/100";
                        if ($state <= 40) return "🟡 {$state}/100";

                        return "❗️ {$state}/100";
                    }),
                Tables\Columns\TextColumn::make('comment')
                    ->label('Summary')
                    ->wrap()
                    ->lineClamp(2),
                Tables\Columns\TextColumn::make('risk_score_comment')
                    ->label('Comment')
                    ->wrap()
                    ->lineClamp(2),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn ($record) => "Contract Clause - {$record->title}"),
            ])
            ->bulkActions([
                //
            ]);
    }
}
