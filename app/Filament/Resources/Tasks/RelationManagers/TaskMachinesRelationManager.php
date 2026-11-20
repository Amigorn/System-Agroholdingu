<?php

namespace App\Filament\Resources\Tasks\RelationManagers;

use App\Models\Machine;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TaskMachinesRelationManager extends RelationManager
{
    protected static string $relationship = 'taskMachines';

    protected static ?string $title = 'Zestawy robocze';

    /**
     * Zwraca ID innych (nieukończonych) zadań, których przedział czasowy
     * nakłada się z przedziałem bieżącego zadania.
     *
     * @return array<int>
     */
    protected function getConflictingTaskIds(): array
    {
        $owner = $this->getOwnerRecord();
        if (! $owner) {
            return [];
        }

        $ownerStartRaw = $owner->planned_at ?? $owner->started_at ?? $owner->created_at;
        if (! $ownerStartRaw) {
            return [];
        }

        $ownerStart = Carbon::parse($ownerStartRaw);
        $ownerEndRaw = $owner->planned_end_at ?? $owner->completed_at;
        $ownerEnd = $ownerEndRaw ? Carbon::parse($ownerEndRaw) : $ownerStart->copy()->addHours(2);

        if ($ownerEnd->lte($ownerStart)) {
            $ownerEnd = $ownerStart->copy()->addHours(2);
        }

        $candidates = DB::table('tasks')
            ->where('id', '!=', $owner->id)
            ->where('status', '!=', 'completed')
            ->get(['id', 'planned_at', 'planned_end_at', 'started_at', 'completed_at', 'created_at']);

        $conflicts = [];

        foreach ($candidates as $other) {
            $otherStartRaw = $other->planned_at ?? $other->started_at ?? $other->created_at;
            if (! $otherStartRaw) {
                continue;
            }

            $otherStart = Carbon::parse($otherStartRaw);
            $otherEndRaw = $other->planned_end_at ?? $other->completed_at;
            $otherEnd = $otherEndRaw ? Carbon::parse($otherEndRaw) : $otherStart->copy()->addHours(2);

            if ($otherEnd->lte($otherStart)) {
                $otherEnd = $otherStart->copy()->addHours(2);
            }

            // Dwa przedziały [A_start, A_end) i [B_start, B_end) nachodzą na siebie,
            // gdy A_start < B_end oraz A_end > B_start.
            if ($ownerStart->lt($otherEnd) && $ownerEnd->gt($otherStart)) {
                $conflicts[] = (int) $other->id;
            }
        }

        return $conflicts;
    }

    /**
     * Zwraca ID zasobów zajętych w kolidujących czasowo zadaniach
     * oraz w innych zestawach roboczych tego samego zadania.
     *
     * @return array<int>
     */
    protected function getBusyResourceIds(string $column, ?Model $record): array
    {
        $currentTaskId = $this->getOwnerRecord()?->id;
        $conflictingTaskIds = $this->getConflictingTaskIds();

        $busyIds = [];

        // 1. Zasoby przypisane w INNYCH zadaniach, które nakładają się czasowo z bieżącym zadaniem
        if ($conflictingTaskIds !== []) {
            $fromConflictingTasks = DB::table('task_machines')
                ->whereIn('task_id', $conflictingTaskIds)
                ->pluck($column)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            $busyIds = array_merge($busyIds, $fromConflictingTasks);
        }

        // 2. Zasoby użyte już w INNYCH zestawach roboczych TEGO SAMEGO zadania
        if ($currentTaskId) {
            $fromSameTask = DB::table('task_machines')
                ->where('task_id', $currentTaskId)
                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                ->pluck($column)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            $busyIds = array_merge($busyIds, $fromSameTask);
        }

        // 3. Przy edycji bieżącego rekordu pozwól na zachowanie jego dotychczasowej wartości
        if ($record && ! empty($record->{$column})) {
            $busyIds = array_values(array_diff($busyIds, [(int) $record->{$column}]));
        }

        return array_values(array_unique($busyIds));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tractor_id')
                    ->label('Ciągnik / Kombajn (Jednostka napędowa)')
                    ->options(function (?Model $record): array {
                        $busyTractorIds = $this->getBusyResourceIds('tractor_id', $record);

                        return Machine::query()
                            ->whereIn('type', ['Ciągnik', 'Kombajn'])
                            ->whereNotIn('id', $busyTractorIds)
                            ->get()
                            ->mapWithKeys(fn ($machine) => [$machine->id => "{$machine->type} - {$machine->name}"])
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (callable $set, $state) {
                        $machine = Machine::find($state);
                        if ($machine && $machine->type === 'Kombajn') {
                            $set('machine_id', null);
                        }
                    }),

                Select::make('machine_id')
                    ->label('Maszyna towarzysząca (Narzędzie)')
                    ->options(function (?Model $record): array {
                        $busyMachineIds = $this->getBusyResourceIds('machine_id', $record);

                        return Machine::query()
                            ->whereNotIn('type', ['Ciągnik', 'Kombajn'])
                            ->whereNotIn('id', $busyMachineIds)
                            ->get()
                            ->mapWithKeys(fn ($machine) => [$machine->id => "{$machine->type} - {$machine->name}"])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->disabled(function (callable $get): bool {
                        $tractorId = $get('tractor_id');
                        if (! $tractorId) {
                            return false;
                        }

                        $machine = Machine::find($tractorId);

                        return $machine && $machine->type === 'Kombajn';
                    })
                    ->required(function (callable $get): bool {
                        $tractorId = $get('tractor_id');
                        if (! $tractorId) {
                            return false;
                        }

                        $machine = Machine::find($tractorId);

                        return $machine && $machine->type === 'Ciągnik';
                    })
                    ->placeholder(function (callable $get): string {
                        $tractorId = $get('tractor_id');
                        if ($tractorId) {
                            $machine = Machine::find($tractorId);
                            if ($machine && $machine->type === 'Kombajn') {
                                return 'Kombajn nie wymaga maszyny towarzyszącej';
                            }
                        }

                        return 'Wybierz maszynę towarzyszącą';
                    }),

                Select::make('user_id')
                    ->label('Operator / Kierowca')
                    ->options(function (?Model $record): array {
                        $busyUserIds = $this->getBusyResourceIds('user_id', $record);

                        return User::query()
                            ->where('role', 'worker')
                            ->whereNotIn('id', $busyUserIds)
                            ->get()
                            ->mapWithKeys(fn ($user) => [$user->id => "{$user->first_name} {$user->last_name}"])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->placeholder('-- Wybierz operatora --'),

                Textarea::make('notes')
                    ->label('Uwagi / Rola do zestawu')
                    ->placeholder('np. Główny agregat, uwaga na podmokły rów wschodni')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('tractor.name')
                    ->label('Ciągnik / Kombajn')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('machine.name')
                    ->label('Maszyna towarzysząca')
                    ->placeholder('Brak (sam pojazd)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user')
                    ->label('Operator')
                    ->formatStateUsing(fn ($record) => $record->user ? "{$record->user->first_name} {$record->user->last_name}" : 'Nie przypisano')
                    ->badge()
                    ->color(fn ($state, $record) => $record->user ? 'success' : 'gray'),

                TextColumn::make('notes')
                    ->label('Uwagi')
                    ->limit(40)
                    ->placeholder('Brak uwag'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Dodaj zestaw roboczy')
                    ->modalHeading('Dodaj nowy zestaw roboczy'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edytuj')
                    ->modalHeading('Edytuj zestaw')
                    ->modalSubmitActionLabel('Zapisz'),
                DeleteAction::make(),
            ]);
    }
}