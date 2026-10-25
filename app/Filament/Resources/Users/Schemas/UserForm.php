<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label('Imię')
                    ->required(),
                    
                TextInput::make('last_name')
                    ->label('Nazwisko')
                    ->required(),
                    
                TextInput::make('email')
                    ->label('Adres email')
                    ->email()
                    ->required(),
                    
                DateTimePicker::make('email_verified_at')
                    ->label('Data weryfikacji email'),
                    
                TextInput::make('password')
                    ->label('Hasło')
                    ->password()
                    // Wymagane tylko przy tworzeniu nowego konta
                    ->required(fn (string $context): bool => $context === 'create')
                    // Ignoruj zapis do bazy, jeśli pole podczas edycji zostało puste
                    ->dehydrated(fn ($state) => filled($state)),
                    
                Select::make('role')
                    ->label('Rola w systemie')
                    ->options([
                        'admin' => 'Administrator',
                        'worker' => 'Pracownik',
                    ])
                    ->required()
                    ->default('worker'),
            ]);
    }
}