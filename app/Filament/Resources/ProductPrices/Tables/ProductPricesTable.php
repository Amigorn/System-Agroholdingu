<?php

namespace App\Filament\Resources\ProductPrices\Tables;

use App\Filament\Resources\ProductPrices\ProductPriceResource;
use App\Models\Harvest;
use App\Models\InvoiceItem;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ProductPricesTable
{
    protected static function shouldBlockPriceDeletion($record): bool
    {
        $today = now()->toDateString();
        $validFrom = $record->valid_from?->toDateString() ?? (string) $record->valid_from;
        $validUntil = $record->valid_until ? ($record->valid_until?->toDateString() ?? (string) $record->valid_until) : null;

        $isCurrentPrice = $validFrom <= $today
            && ($validUntil === null || $validUntil >= $today);

        if (! $isCurrentPrice) {
            return false;
        }

        $productId = $record->product_id;

        if (! $productId) {
            return false;
        }

        $hasInvoices = class_exists(InvoiceItem::class)
            && Schema::hasTable('invoice_items')
            && InvoiceItem::query()->where('product_id', $productId)->exists();

        $hasHarvests = class_exists(Harvest::class)
            && Schema::hasTable('harvests')
            && Harvest::query()->where('product_id', $productId)->exists();

        if ($hasInvoices || $hasHarvests) {
            Notification::make()
                ->danger()
                ->title('Nie można usunąć aktualnego cennika')
                ->body('Ten cennik aktualnie obowiązuje dla produktu powiązanego z fakturami lub zbiorami. Zakończ jego ważność zamiast usuwać.')
                ->send();

            return true;
        }

        return false;
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produkt')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Cena za')
                    ->alignRight()
                    ->money('PLN')
                    ->sortable(),
                TextColumn::make('unit.name')
                    ->label('Jednostka')
                    ->placeholder('—')
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
            ->defaultSort('valid_from', 'desc')
            ->filters([
                Filter::make('only_active')
                    ->label('Tylko aktualnie obowiązujące')
                    ->default()
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
                    ->before(function ($record, Action $action): void {
                        if (static::shouldBlockPriceDeletion($record)) {
                            $action->halt();
                        }
                    })
                    ->action(function ($record, Action $action): void {
                        if (static::shouldBlockPriceDeletion($record)) {
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