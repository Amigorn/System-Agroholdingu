<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nazwa magazynu')
                    ->placeholder('np. Magazyn Nawozów Sztucznych')
                    ->required()
                    ->maxLength(255),

                TextInput::make('location')
                    ->label('Lokalizacja / Opis miejsca')
                    ->placeholder('np. Gospodarstwo - sektor C')
                    ->maxLength(255),
            ]);
    }
}