<?php

namespace App\Filament\Resources\Harvests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HarvestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->label('Data zbioru')
                    ->required(),

                TextInput::make('quantity')
                    ->label('Ilość')
                    ->required()
                    ->numeric(),

                Select::make('unit_id')
                    ->label('Jednostka')
                    ->relationship('unit', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('crop_id')
                    ->label('Uprawa')
                    ->relationship('crop', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('field_id')
                    ->label('Pole uprawne')
                    ->relationship('field', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('product_id')
                    ->label('Produkt')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }
}