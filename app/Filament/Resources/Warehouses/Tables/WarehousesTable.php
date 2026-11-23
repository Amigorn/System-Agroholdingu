<?php

namespace App\Filament\Resources\Warehouses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\Warehouses\WarehouseResource;
use Filament\Tables\Table;

class WarehousesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa magazynu')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('category')
                    ->label('Kategoria')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Lokalizacja')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('updated_at')
                    ->label('Zaktualizowano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => WarehouseResource::getUrl('edit', [
                    'record' => $record,
                ])),
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Usuń Magazyn')
                    ->modalDescription('Czy na pewno chcesz to zrobić?')
                    ->modalSubmitActionLabel('Tak, usuń')
                    ->action(fn ($record) => $record->delete()),
            ])
            ->toolbarActions([
                    //
            ]);
    }
}
