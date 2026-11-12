<?php

namespace App\Filament\Resources\Tasks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Filament\Resources\Tasks\TaskResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->orderByRaw("FIELD(status, 'in_progress', 'planned', 'completed')")
            )
            ->columns([
                TextColumn::make('type')
                    ->label('Zabieg')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('field.name')
                    ->label('Pole')
                    ->sortable(),
                TextColumn::make('user.last_name')
                    ->label('Pracownik')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => "{$record->user?->first_name} {$record->user?->last_name}"),
                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
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
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('completed_at')
                    ->label('Data zakończenia')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('tractor.name')
                    ->label('Ciągnik')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('machine.name')
                    ->label('Maszyna towarzysząca')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label('Opis')
                    ->toggleable(isToggledHiddenByDefault: true),
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
            SelectFilter::make('status')
                ->label('Status zadania')
                ->options([
                    'planned' => 'Zaplanowane',
                    'in_progress' => 'W toku',
                    'completed' => 'Zakończone',
                ])
                ->native(false),        // Ładniejszy select w stylu Filamenta
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => TaskResource::getUrl('edit', [
                    'record' => $record,
                ])),
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Usuń Zadanie')
                    ->modalDescription('Czy na pewno chcesz to zrobić?')
                    ->modalSubmitActionLabel('Tak, usuń')
                    ->action(fn ($record) => $record->delete()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}