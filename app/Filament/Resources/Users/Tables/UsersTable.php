<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use App\Models\User;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('Imię')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('last_name')
                    ->label('Nazwisko')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('email')
                    ->label('Adres email')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('email_verified_at')
                    ->label('Zweryfikowano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                    
                TextColumn::make('role')
                    ->label('Rola')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',   // Czerwony dla Admina
                        'manager' => 'info',   // Niebieski dla Menadzera
                        'worker' => 'success', // Zielony dla Pracownika
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Administrator',
                        'manager' => 'Menadżer',
                        'worker' => 'Pracownik',
                        default => $state,
                    })
                    ->searchable(),
                    
                TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('updated_at')
                    ->label('Zaktualizowano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Usuń wybrane'), // Polskie akcje masowe
                ])->label('Akcje masowe'),
            ]);
    }
}