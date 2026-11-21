<?php

namespace App\Filament\Resources\Crops\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CropForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nazwa uprawy')
                    ->required()
                    ->maxLength(255),

                Select::make('season_id')
                    ->label('Sezon uprawowy')
                    ->relationship('season', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('field_id')
                    ->label('Pole uprawne')
                    ->relationship('field', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                DatePicker::make('sowing_date')
                    ->label('Data siewu'),

                Textarea::make('description')
                    ->label('Opis'),
            ]);
    }
}