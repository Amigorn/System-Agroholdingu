<?php

namespace App\Filament\Resources\ProductPrices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use App\Filament\Resources\ProductPrices\ProductPriceResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ProductPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produkt')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Cena')
                    ->alignRight()
                    ->money('PLN')
                    ->sortable(),

                TextColumn::make('valid_from')
                    ->label('Obowiązuje od')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->label('Obowiązuje do')
                    ->date('d.m.Y')
                    ->placeholder('Bezterminowo')
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
            ->defaultSort('valid_from', 'desc')
            ->filters([
                Filter::make('only_active')
                    ->label('Tylko aktualnie obowiązujące')
                    ->default() // Domyślnie zaznaczony po wejściu na stronę
                    ->query(function (Builder $query): Builder {
                        $today = now()->toDateString();
                        
                        return $query
                            ->where('valid_from', '<=', $today)
                            ->where(function ($q) use ($today) {
                                $q->whereNull('valid_until')
                                  ->orWhere('valid_until', '>=', $today);
                            });
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => ProductPriceResource::getUrl('edit', [
                        'record' => $record,
                    ])),
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Usuń Cennik')
                    ->modalDescription('Czy na pewno chcesz to zrobić?')
                    ->modalSubmitActionLabel('Tak, usuń')
                    ->action(fn ($record) => $record->delete()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}