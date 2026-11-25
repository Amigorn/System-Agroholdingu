<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Harvest;
use App\Models\InvoiceItem;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class ProductsTable
{
    protected static function shouldBlockProductDeletion($record): bool
    {
        $productId = $record->getKey();

        $inInvoiceItems = class_exists(InvoiceItem::class)
            && Schema::hasTable('invoice_items')
            && InvoiceItem::query()->where('product_id', $productId)->exists();

        $inHarvests = class_exists(Harvest::class)
            && Schema::hasTable('harvests')
            && Harvest::query()->where('product_id', $productId)->exists();

        if ($inInvoiceItems || $inHarvests) {
            Notification::make()
                ->danger()
                ->title('Nie można usunąć produktu')
                ->body('Ten produkt jest powiązany z fakturami lub zbiorami w systemie.')
                ->send();

            return true;
        }

        return false;
    }

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
                    ->before(function ($record, Action $action): void {
                        if (static::shouldBlockProductDeletion($record)) {
                            $action->halt();
                        }
                    })
                    ->action(function ($record, Action $action): void {
                        if (static::shouldBlockProductDeletion($record)) {
                            $action->halt();

                            return;
                        }

                        $record->delete();
                    }),
            ])
            ->toolbarActions([
                    //
            ]);
    }
}