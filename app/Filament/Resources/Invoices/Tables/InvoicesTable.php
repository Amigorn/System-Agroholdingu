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
                TextColumn::make('number')
                    ->label('Numer faktury')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('issue_date')
                    ->label('Data wystawienia')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Typ dokumentu')
                    ->badge()
                    ->colors([
                        'danger' => 'purchase',    // Zakup na czerwono
                        'success' => 'sale',       // Sprzedaż na zielono
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'purchase' => 'Zakup (Koszt)',
                        'sale' => 'Sprzedaż (Przychód)',
                        default => $state,
                    })
                    ->searchable(),

                TextColumn::make('contractor')
                    ->label('Kontrahent')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total')
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
                // Filtry
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