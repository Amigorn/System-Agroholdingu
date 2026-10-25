<?php

namespace App\Filament\Resources\Machines\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class MachineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nazwa maszyny')
                ->required(),
            TextInput::make('type')
                ->label('Typ sprzętu'),
            Select::make('status')
                ->label('Status')
                ->options([
                    'available' => 'Dostępna',
                    'in_use' => 'W użyciu',
                    'under_repair' => 'W naprawie',
                ])
                ->required(),
            Textarea::make('description')
                ->label('Uwagi techniczne')
                ->columnSpanFull(),
        ]);
    }
}