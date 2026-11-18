<?php

namespace App\Filament\Resources\Tasks\RelationManagers;

use App\Models\Machine;
use App\Models\User;
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

class TaskMachinesRelationManager extends RelationManager
{
    protected static string $relationship = 'taskMachines';

    protected static ?string $title = 'Zestawy robocze (Ciągnik + Maszyna + Operator)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tractor_id')
                    ->label('Ciągnik / Kombajn (Jednostka napędowa)')
                    ->options(function (?Model $record): array {
                        $currentTaskId = $this->ownerRecord?->id;

                        // 1. Ciągniki zajęte w INNYCH aktywnych zadaniach
                        $busyTractorIds = \DB::table('task_machines')
                            ->join('tasks', 'tasks.id', '=', 'task_machines.task_id')
                            ->where('tasks.status', '!=', 'completed')
                            ->when($currentTaskId, fn ($query) => $query->where('tasks.id', '!=', $currentTaskId))
                            ->pluck('task_machines.tractor_id')
                            ->filter()
                            ->toArray();

                        // 2. Ciągniki użyte już w INNYCH zestawach TEGO SAMEGO zadania
                        if ($currentTaskId) {
                            $currentTaskTractors = \DB::table('task_machines')
                                ->where('task_id', $currentTaskId)
                                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                ->pluck('tractor_id')
                                ->filter()
                                ->toArray();

                            $busyTractorIds = array_merge($busyTractorIds, $currentTaskTractors);
                        }

                        if ($record && $record->tractor_id) {
                            $busyTractorIds = array_diff($busyTractorIds, [$record->tractor_id]);
                        }

                        return Machine::whereIn('type', ['Ciągnik', 'Kombajn'])
                            ->whereNotIn('id', array_unique($busyTractorIds))
                            ->get()
                            ->mapWithKeys(fn ($machine) => [$machine->id => "{$machine->type} - {$machine->name}"])
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live() // <--- Włączamy reaktywność, aby pole 'machine_id' reagowało na zmianę
                    ->afterStateUpdated(function (callable $set, $state) {
                        // Jeśli użytkownik zmieni pojazd na kombajn, automatycznie czścimy maszynę towarzyszącą
                        $machine = Machine::find($state);
                        if ($machine && $machine->type === 'Kombajn') {
                            $set('machine_id', null);
                        }
                    }),

                Select::make('machine_id')
                    ->label('Maszyna towarzysząca (Narzędzie)')
                    ->options(function (?Model $record): array {
                        $currentTaskId = $this->ownerRecord?->id;

                        // 1. Maszyny zajęte w INNYCH aktywnych zadaniach
                        $busyMachineIds = \DB::table('task_machines')
                            ->join('tasks', 'tasks.id', '=', 'task_machines.task_id')
                            ->where('tasks.status', '!=', 'completed')
                            ->when($currentTaskId, fn ($query) => $query->where('tasks.id', '!=', $currentTaskId))
                            ->pluck('task_machines.machine_id')
                            ->filter()
                            ->toArray();

                        // 2. Maszyny użyte już w INNYCH zestawach TEGO SAMEGO zadania
                        if ($currentTaskId) {
                            $currentTaskMachines = \DB::table('task_machines')
                                ->where('task_id', $currentTaskId)
                                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                ->pluck('machine_id')
                                ->filter()
                                ->toArray();

                            $busyMachineIds = array_merge($busyMachineIds, $currentTaskMachines);
                        }

                        if ($record && $record->machine_id) {
                            $busyMachineIds = array_diff($busyMachineIds, [$record->machine_id]);
                        }

                        return Machine::whereNotIn('type', ['Ciągnik', 'Kombajn'])
                            ->whereNotIn('id', array_unique($busyMachineIds))
                            ->get()
                            ->mapWithKeys(fn ($machine) => [$machine->id => "{$machine->type} - {$machine->name}"])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    // Blokujemy pole i wymuszamy brak wartości, jeśli wybrano Kombajn
                    ->disabled(function (callable $get): bool {
                        $tractorId = $get('tractor_id');
                        if (!$tractorId) return false;
                        
                        $machine = Machine::find($tractorId);
                        return $machine && $machine->type === 'Kombajn';
                    })
                    // Wymagamy maszyny tylko wtedy, gdy wybrano Ciągnik
                    ->required(function (callable $get): bool {
                        $tractorId = $get('tractor_id');
                        if (!$tractorId) return false;
                        
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
                        $currentTaskId = $this->ownerRecord?->id;

                        // 1. Operatorzy zajęci w INNYCH aktywnych zadaniach
                        $busyUserIds = \DB::table('task_machines')
                            ->join('tasks', 'tasks.id', '=', 'task_machines.task_id')
                            ->where('tasks.status', '!=', 'completed')
                            ->when($currentTaskId, fn ($query) => $query->where('tasks.id', '!=', $currentTaskId))
                            ->pluck('task_machines.user_id')
                            ->filter()
                            ->toArray();

                        // 2. Operatorzy przypisani już w INNYCH zestawach TEGO SAMEGO zadania
                        if ($currentTaskId) {
                            $currentTaskUsers = \DB::table('task_machines')
                                ->where('task_id', $currentTaskId)
                                ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                ->pluck('user_id')
                                ->filter()
                                ->toArray();

                            $busyUserIds = array_merge($busyUserIds, $currentTaskUsers);
                        }

                        if ($record && $record->user_id) {
                            $busyUserIds = array_diff($busyUserIds, [$record->user_id]);
                        }

                        return User::query()
                            ->where('role', 'worker') 
                            ->whereNotIn('id', array_unique($busyUserIds))
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
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}