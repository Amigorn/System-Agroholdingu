<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('type')
                ->label('Rodzaj zabiegu')
                ->datalist([
                    'Nawożenie',
                    'Oprysk',
                    'Orka',
                    'Siew',
                ])
                ->required(),
            Select::make('field_id')
                ->label('Pole uprawne')
                ->relationship('field', 'name')
                ->required()
                ->searchable()
                ->preload(),
            Select::make('status')
                ->label('Status')
                ->default('planned')
                ->options([
                    'planned' => 'Zaplanowane',
                    'in_progress' => 'W toku',
                    'completed' => 'Zakończone',
                ])
                ->required(),
            DateTimePicker::make('planned_at')
                ->label('Data planowana')
                ->required(),
            DateTimePicker::make('completed_at')
                ->label('Data zakończenia'),
            Textarea::make('description')
                ->label('Opis zadania')
                ->columnSpanFull(),
        ]);
    }
}