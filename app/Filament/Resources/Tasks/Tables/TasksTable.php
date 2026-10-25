<?php

namespace App\Filament\Resources\Tasks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('type')
                ->label('Zabieg')
                ->searchable(),
            TextColumn::make('field.name')
                ->label('Pole')
                ->sortable(),
            TextColumn::make('user.last_name')
                ->label('Pracownik')
                ->formatStateUsing(fn ($record) => "{$record->user?->first_name} {$record->user?->last_name}"),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'planned' => 'info',
                    'in_progress' => 'warning',
                    'completed' => 'success',
                    default => 'gray',
                }),
            TextColumn::make('planned_at')
                ->label('Data planowana')
                ->dateTime('Y-m-d H:i')
                ->sortable(),
            TextColumn::make('completed_at')
                ->label('Data zakończenia')
                ->dateTime('Y-m-d H:i')
                ->sortable(),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
