<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    protected static ?string $title = 'Historia cennika i stawki';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('price')
                    ->label('Cena (PLN)')
                    ->numeric()
                    ->prefix('PLN')
                    ->required()
                    ->step(0.01),

                DatePicker::make('valid_from')
                    ->label('Obowiązuje od')
                    ->required()
                    ->default(now()),

                DatePicker::make('valid_until')
                    ->label('Obowiązuje do')
                    ->placeholder('Pozostaw puste, jeśli bezterminowo'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('price')
            ->columns([
                TextColumn::make('price')
                    ->label('Cena')
                    ->money('PLN')
                    ->sortable(),

                TextColumn::make('valid_from')
                    ->label('Od daty')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->label('Do daty')
                    ->date('d.m.Y')
                    ->placeholder('Brak (aktualna/bezterminowa)')
                    ->sortable(),
            ])
            ->defaultSort('valid_from', 'desc')
            ->headerActions([
                CreateAction::make()->label('Dodaj cenę historyczną/nową'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}