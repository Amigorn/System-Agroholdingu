<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\Action;
use App\Filament\Resources\Users\UserResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
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
                        'manager' => 'Magazynier',
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
                TrashedFilter::make()->label('Status usunięcia'),
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => UserResource::getUrl('edit', [
                        'record' => $record,
                    ])),

                // Standardowe miękkie usunięcie (przeniesienie do kosza)
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Usuń Użytkownika')
                    ->modalDescription('Czy na pewno chcesz przenieść tego użytkownika do kosza?')
                    ->modalSubmitActionLabel('Tak, usuń')
                    ->visible(fn ($record) => !$record->trashed())
                    ->action(fn ($record) => $record->delete()),

                // Przywrócenie z kosza gotową akcją Filamenta
                RestoreAction::make()
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->button()
                    ->label(false),

                // Trwałe usunięcie z bazy gotową akcją Filamenta
                ForceDeleteAction::make()
                    ->icon('heroicon-m-x-circle')
                    ->button()
                    ->label(false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Usuń wybrane'),
                    RestoreBulkAction::make()->label('Przywróć wybrane'),
                    ForceDeleteBulkAction::make()->label('Usuń trwale wybrane'),
                ])->label('Akcje masowe'),
            ]);
    }
}