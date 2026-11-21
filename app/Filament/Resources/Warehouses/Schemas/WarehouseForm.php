<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nazwa magazynu')
                    ->placeholder('np. Magazyn Jasson')
                    ->required()
                    ->maxLength(255),

                Select::make('category')
                    ->label('Kategoria magazynu')
                    ->options([
                        'Nawozów' => 'Nawozów',
                        'Paliw' => 'Paliw',
                        'Nasion' => 'Nasion',
                        'Zbożowy' => 'Zbożowy',
                        'Maszynowy' => 'Maszynowy',
                    ])
                    ->required()
                    ->native(false),

                TextInput::make('location')
                    ->label('Lokalizacja / Opis miejsca')
                    ->placeholder('np. Gospodarstwo - sektor C')
                    ->maxLength(255),
            ]);
    }
}
