<?php

namespace App\Filament\Resources\Crops\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CropForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nazwa')
                    ->required(),
                Textarea::make('opis')
                    ->columnSpanFull(),
                TextInput::make('pole_id')
                    ->required()
                    ->numeric(),
                TextInput::make('sezon_uprawowy_id')
                    ->required()
                    ->numeric(),
            ]);
    }
}
