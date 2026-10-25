<?php

namespace App\Filament\Resources\Harvests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HarvestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('date')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                Select::make('unit_id')
                    ->relationship('unit', 'name')
                    ->required(),
                Select::make('crop_id')
                    ->relationship('crop', 'name')
                    ->required(),
                Select::make('field_id')
                    ->relationship('field', 'name')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
            ]);
    }
}