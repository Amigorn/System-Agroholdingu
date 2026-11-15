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

                        $busyTractorIds = \DB::table('task_machines')
                            ->join('tasks', 'tasks.id', '=', 'task_machines.task_id')
                            ->where('tasks.status', '!=', 'completed')
                            ->when($currentTaskId, fn ($query) => $query->where('tasks.id', '!=', $currentTaskId))
                            ->pluck('task_machines.tractor_id')
                            ->filter()
                            ->toArray();

                        if ($record && $record->tractor_id) {
                            $busyTractorIds = array_diff($busyTractorIds, [$record->tractor_id]);
                        }

                        return Machine::whereIn('type', ['Ciągnik', 'Kombajn'])
                            ->whereNotIn('id', $busyTractorIds)
                            ->get()
                            ->mapWithKeys(fn ($machine) => [$machine->id => "{$machine->type} - {$machine->name}"])
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('machine_id')
                    ->label('Maszyna towarzysząca (Narzędzie)')
                    ->options(function (?Model $record): array {
                        $currentTaskId = $this->ownerRecord?->id;

                        $busyMachineIds = \DB::table('task_machines')
                            ->join('tasks', 'tasks.id', '=', 'task_machines.task_id')
                            ->where('tasks.status', '!=', 'completed')
                            ->when($currentTaskId, fn ($query) => $query->where('tasks.id', '!=', $currentTaskId))
                            ->pluck('task_machines.machine_id')
                            ->filter()
                            ->toArray();

                        if ($record && $record->machine_id) {
                            $busyMachineIds = array_diff($busyMachineIds, [$record->machine_id]);
                        }

                        return Machine::whereNotIn('type', ['Ciągnik', 'Kombajn'])
                            ->whereNotIn('id', $busyMachineIds)
                            ->get()
                            ->mapWithKeys(fn ($machine) => [$machine->id => "{$machine->type} - {$machine->name}"])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->placeholder('-- Sam pojazd (np. sam transport) --'),

                Select::make('user_id')
                    ->label('Operator / Kierowca')
                    ->options(function (?Model $record): array {
                        $currentTaskId = $this->ownerRecord?->id;

                        // Pobieramy ID operatorów zajętych w innych aktywnych zadaniach
                        $busyUserIds = \DB::table('task_machines')
                            ->join('tasks', 'tasks.id', '=', 'task_machines.task_id')
                            ->where('tasks.status', '!=', 'completed')
                            ->when($currentTaskId, fn ($query) => $query->where('tasks.id', '!=', $currentTaskId))
                            ->pluck('task_machines.user_id')
                            ->filter()
                            ->toArray();

                        if ($record && $record->user_id) {
                            $busyUserIds = array_diff($busyUserIds, [$record->user_id]);
                        }

                        return User::query()
                            // Filtrujemy po kolumnie 'role' w tabeli users (dostosuj nazwę kolumny, jeśli u Ciebie nazywa się inaczej, np. 'type' lub 'position')
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
                    ->placeholder('Brak (sam ciągnik)')
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
                CreateAction::make()->label('Dodaj zestaw roboczy'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}