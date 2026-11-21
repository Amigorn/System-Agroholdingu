<?php

namespace App\Filament\Resources\Machines\RelationManagers;

use App\Models\TaskMachine;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasksAsMachine'; 

    protected static ?string $title = 'Historia użycia maszyny w zadaniach';

    // Kluczowa zmiana: pobieramy wpisy dla maszyny jako ciągnik LUB jako maszyna towarzysząca
    public function getTableQuery(): Builder
    {
        $machineId = $this->getOwnerRecord()->id;

        return TaskMachine::query()
            ->where(function (Builder $query) use ($machineId) {
                $query->where('machine_id', $machineId)
                      ->orWhere('tractor_id', $machineId);
            })
            ->whereHas('task', function (Builder $q) {
                $q->where('status', 'completed');
            })
            ->with(['task.field', 'user', 'tractor', 'machine']);
    }

    // Widok szczegółów (podgląd) oparty na Infolist i TextEntry
    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- INFORMACJE Z ZADANIA (TASK) ---
                TextEntry::make('task.type')
                    ->label('Typ zabiegu'),

                TextEntry::make('task.field.name')
                    ->label('Pole uprawne')
                    ->placeholder('Brak pola'),

                TextEntry::make('task.planned_at')
                    ->label('Planowane rozpoczęcie')
                    ->dateTime('Y-m-d H:i'),

                TextEntry::make('task.planned_end_at')
                    ->label('Planowane zakończenie')
                    ->dateTime('Y-m-d H:i'),

                TextEntry::make('task.started_at')
                    ->label('Data rozpoczęcia')
                    ->dateTime('Y-m-d H:i'),

                TextEntry::make('task.completed_at')
                    ->label('Data zakończenia')
                    ->dateTime('Y-m-d H:i'),
                
                TextEntry::make('task.status')
                    ->label('Status zadania')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Zakończone',
                        'in_progress' => 'W toku',
                        'planned' => 'Zaplanowane',
                        default => $state,
                    }),

                TextEntry::make('task.description')
                    ->label('Opis / Instrukcja zadania')
                    ->placeholder('Brak opisu'),

                // --- INFORMACJE Z ZESTAWU ROBOCZEGO (TASK_MACHINE) ---
                TextEntry::make('tractor.name')
                    ->label('Użyty ciągnik / Kombajn')
                    ->placeholder('Brak'),

                TextEntry::make('machine.name')
                    ->label('Użyta maszyna towarzysząca')
                    ->placeholder('Brak'),

                TextEntry::make('user')
                    ->label('Operator / Kierowca')
                    ->formatStateUsing(fn ($record) => $record->user ? "{$record->user->first_name} {$record->user->last_name}" : 'Nie przypisano'),

                TextEntry::make('notes')
                    ->label('Uwagi do zestawu / Rola')
                    ->placeholder('Brak uwag'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('task.type')
                    ->label('Zabieg')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('task.field.name')
                    ->label('Pole uprawne')
                    ->sortable()
                    ->placeholder('Brak pola'),

                TextColumn::make('user')
                    ->label('Operator')
                    ->formatStateUsing(fn ($record) => $record->user ? "{$record->user->first_name} {$record->user->last_name}" : 'Nie przypisano')
                    ->badge()
                    ->color(fn ($record) => $record->user ? 'success' : 'gray'),

                TextColumn::make('task.status')
                    ->label('Status')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn () => 'Zakończone'),

                TextColumn::make('task.completed_at')
                    ->label('Data zakończenia')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Szczegóły')
                    ->modalHeading('Podgląd szczegółowy'),
            ]);
    }
}