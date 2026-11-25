<?php

namespace App\Filament\Resources\ProductPrices\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Produkt')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                        if (! $state || filled($get('unit_id'))) {
                            return;
                        }

                        $product = Product::find($state);
                        $defaultUnitId = $product?->unit_id
                            ?? $product?->unit_of_measure_id
                            ?? $product?->unit?->id
                            ?? $product?->unitOfMeasure?->id;

                        if ($defaultUnitId) {
                            $set('unit_id', $defaultUnitId);
                        }
                    }),

                TextInput::make('price')
                    ->label('Cena')
                    ->numeric()
                    ->prefix('PLN')
                    ->required()
                    ->step(0.01),

                Select::make('unit_id')
                    ->label('Jednostka')
                    ->relationship('unit', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Wybierz jednostkę (np. t, kg, l)'),

                DatePicker::make('valid_from')
                    ->label('Obowiązuje od')
                    ->required()
                    ->default(now()),

                DatePicker::make('valid_until')
                    ->label('Obowiązuje do')
                    ->nullable()
                    ->placeholder('Bezterminowo (zostaw puste)'),
            ]);
    }
}