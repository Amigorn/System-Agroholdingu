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
            Select::make('user_id')
                ->label('Pracownik')
                ->relationship('user', 'email')
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                ->required()
                ->searchable(),
            Select::make('field_id')
                ->label('Pole uprawne')
                ->relationship('field', 'name')
                ->required(),
            Select::make('machine_id')
                ->label('Przypisana maszyna')
                ->relationship('machine', 'name'),
            TextInput::make('type')
                ->label('Rodzaj zabiegu')
                ->required(),
            Select::make('status')
                ->label('Status')
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