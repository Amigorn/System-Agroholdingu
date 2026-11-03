<?php

namespace App\Filament\Resources\Crops\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
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

                Textarea::make('opis')
                    ->label('Opis')
                    ->columnSpanFull(),

                Select::make('pole_id')
                    ->label('Pole uprawne')
                    ->relationship('field', 'name') // Zakłada relację 'field' w modelu Crop
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('sezon_uprawowy_id')
                    ->label('Sezon uprawowy')
                    ->relationship('season', 'name') // Zakłada relację 'season' w modelu Crop (zmień 'season' na nazwę swojej relacji, np. 'cropSeason', oraz 'name' na kolumnę wyświetlaną np. 'year')
                    ->required()
                    ->searchable()
                    ->preload(),
            ]);
    }
}