<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nazwa produktu')
                    ->placeholder('np. Pszenica ozima, Jęczmień jary')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->label('Typ produktu')
                    ->options([
                        'plon' => 'Plon / Zbiór',
                        'produkt_przetworzony' => 'Produkt przetworzony',
                    ])
                    ->required()
                    ->native(false),

                Select::make('unit_id')
                    ->label('Jednostka miary')
                    ->relationship('unit', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }
}
