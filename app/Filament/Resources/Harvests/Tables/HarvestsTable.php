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
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Ilość')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit.name')
                    ->label('Jednostka')
                    ->sortable(),
                TextColumn::make('crop.name')
                    ->label('Uprawa')
                    ->sortable(),
                TextColumn::make('field.name')
                    ->label('Pole')
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produkt')
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