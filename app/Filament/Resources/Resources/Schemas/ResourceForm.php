<?php

namespace App\Filament\Resources\Resources\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nazwa zasobu')
                    ->placeholder('np. Saletra amonowa, Olej napędowy, Roundup')
                    ->required()
                    ->maxLength(255),

                Select::make('type')
                    ->label('Typ zasobu')
                    ->options([
                        'paliwo' => 'Paliwo',
                        'nawóz' => 'Nawóz',
                        'środki ochrony roślin' => 'Środki ochrony roślin',
                        'materiał siewny' => 'Materiał siewny',
                        'płyny eksploatacyjne' => 'Płyny eksploatacyjne',
                    ])
                    ->required()
                    ->searchable()
                    ->native(false),

                Select::make('unit_id')
                    ->label('Jednostka miary')
                    ->relationship('unit', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->native(false),
            ]);
    }
}
