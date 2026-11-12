<?php

namespace App\Filament\Resources\ProductPrices;

use App\Filament\Resources\ProductPrices\Pages\CreateProductPrice;
use App\Filament\Resources\ProductPrices\Pages\EditProductPrice;
use App\Filament\Resources\ProductPrices\Pages\ListProductPrices;
use App\Filament\Resources\ProductPrices\Schemas\ProductPriceForm;
use App\Filament\Resources\ProductPrices\Tables\ProductPricesTable;
use App\Models\ProductPrice;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder; // <-- Niezbędne do filtrowania aktualnych cen

class ProductPriceResource extends Resource
{
    protected static ?string $model = ProductPrice::class;

    protected static string | UnitEnum | null $navigationGroup = 'Handel i Finanse';
    protected static ?string $navigationLabel = 'Cennik produktów';
    protected static ?string $pluralModelLabel = 'Cennik produktów';
    protected static ?string $modelLabel = 'Cena';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    public static function form(Schema $schema): Schema
    {
        return ProductPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductPricesTable::configure($table);
    }

    /**
     * Kluczowe: W głównej tabeli pokazujemy TYLKO ceny aktualne na dzisiejszy dzień.
     * Historia i przyszłe stawki znajdują się wewnątrz edycji poszczególnych produktów.
     */
    public static function getEloquentQuery(): Builder
    {
        $today = now()->toDateString();

        return parent::getEloquentQuery()
            ->where('valid_from', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', $today);
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductPrices::route('/'),
            'create' => CreateProductPrice::route('/create'),
            'edit' => EditProductPrice::route('/{record}/edit'),
        ];
    }
}