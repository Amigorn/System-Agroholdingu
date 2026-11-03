<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numer')
                    ->label('Numer faktury')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('data_wystawienia')
                    ->label('Data wystawienia')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('typ')
                    ->label('Typ dokumentu')
                    ->badge()
                    ->colors([
                        'danger' => 'zakup',     // Koszt na czerwono
                        'success' => 'sprzedaz', // Przychód na zielono
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'zakup' => 'Zakup (Koszt)',
                        'sprzedaz' => 'Sprzedaż (Przychód)',
                        default => $state,
                    })
                    ->searchable(),

                TextColumn::make('kontrahent')
                    ->label('Kontrahent')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('wartosc')
                    ->label('Wartość (PLN)')
                    ->money('PLN')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Zaktualizowano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tutaj w przyszłości możesz dodać filtry, np. SelectFilter dla typu
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