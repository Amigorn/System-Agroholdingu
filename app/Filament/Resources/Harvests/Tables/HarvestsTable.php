<?php

namespace App\Filament\Resources\Harvests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HarvestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Data zbioru')
                    ->dateTime()
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Ilość')
                    ->alignRight()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', ' '))
                    ->sortable(),
                TextColumn::make('unit.name')
                    ->label('Jednostka')
                    ->sortable(),
                TextColumn::make('crop.name')
                    ->label('Uprawa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('field.name')
                    ->label('Pole')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produkt')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}