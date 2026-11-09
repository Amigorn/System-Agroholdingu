<?php

namespace App\Filament\Resources\Seasons\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SeasonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('year')
                    ->label('Rok')
                    ->placeholder('np. 2026')
                    ->numeric()
                    ->required()
                    ->minValue(2000)
                    ->maxValue(2100),

                TextInput::make('name')
                    ->label('Nazwa sezonu')
                    ->placeholder('np. Sezon 2026, 2025/2026')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}