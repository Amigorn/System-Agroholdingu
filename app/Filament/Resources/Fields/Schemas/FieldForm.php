<?php

namespace App\Filament\Resources\Fields\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class FieldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nazwa pola')
                ->required()
                ->maxLength(255),
            TextInput::make('area')
                ->label('Powierzchnia (ha)')
                ->numeric()
                ->required(),
            TextInput::make('soil_type')
                ->label('Klasa/Typ gleby'),
            TextInput::make('location')
                ->label('Lokalizacja / Współrzędne'),
            Textarea::make('description')
                ->label('Opis / Uwagi')
                ->columnSpanFull(),
        ]);
    }
}