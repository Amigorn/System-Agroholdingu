<?php

namespace App\Filament\Resources\ProductPrices\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;

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
                    ->preload(),

                TextInput::make('price')
                    ->label('Cena (PLN)')
                    ->numeric()
                    ->prefix('PLN')
                    ->required()
                    ->step(0.01),

                DatePicker::make('valid_from')
                    ->label('Obowiązuje od')
                    ->required()
                    ->default(now()),

                DatePicker::make('valid_until')
                    ->label('Obowiązuje do')
                    ->placeholder('Bezterminowo (zostaw puste)'),
            ]);
    }
}