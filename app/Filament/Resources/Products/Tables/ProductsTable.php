<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa produktu')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'plon' => 'success',
                        'produkt_przetworzony' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'plon' => 'Plon / Zbiór',
                        'produkt_przetworzony' => 'Produkt przetworzony',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('unit.name')
                    ->label('Jednostka miary')
                    ->sortable()
                    ->searchable(),

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
                SelectFilter::make('type')
                    ->label('Filtruj po typie')
                    ->options([
                        'plon' => 'Plon / Zbiór',
                        'produkt_przetworzony' => 'Produkt przetworzony',
                    ]),
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => ProductResource::getUrl('edit', [
                        'record' => $record,
                    ])),

                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Usuń produkt')
                    ->modalDescription('Czy na pewno chcesz usunąć ten produkt?')
                    ->modalSubmitActionLabel('Tak, usuń')
                    ->action(fn ($record) => $record->delete()),
            ])
            ->toolbarActions([
                    //
            ]);
    }
}
