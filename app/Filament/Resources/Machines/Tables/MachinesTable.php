<?php

namespace App\Filament\Resources\Machines\Tables;

use App\Filament\Resources\Machines\MachineResource;
use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Machine;
use App\Models\TaskMachine;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;

class MachinesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Nazwa')
                ->sortable()
                ->searchable(),
            TextColumn::make('type')
                ->label('Typ')
                ->sortable(),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'available' => 'Dostępny',
                    'in_use' => 'W użyciu',
                    'under_repair' => 'W naprawie',
                    default => $state,
                })
                ->color(fn (string $state): string => match ($state) {
                    'available' => 'success',
                    'in_use' => 'warning',
                    'under_repair' => 'danger',
                    default => 'gray',
                }),
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
                    ->label('Status maszyny')
                    ->options([
                        'available' => 'Dostępny',
                        'in_use' => 'W użyciu',
                        'under_repair' => 'W naprawie',
                    ])
                    ->native(false),

                SelectFilter::make('type')
                    ->label('Typ maszyny')
                    ->options(fn () => Machine::distinct()->pluck('type', 'type')->toArray())
                    ->native(false),
            ])
            ->recordActions([
                Action::make('inspectTasks')
                    ->icon('heroicon-m-magnifying-glass')
                    ->button()
                    ->label(false)
                    ->color('info')
                    ->visible(fn (Machine $record): bool => self::hasActiveOrPlannedTasks($record))
                    ->modalHeading(fn (Machine $record): string => "Przypisania maszyny: {$record->name}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Zamknij')
                    ->modalContent(fn (Machine $record): View => self::makeAssignedTasksView($record)),

                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => MachineResource::getUrl('edit', [
                        'record' => $record,
                    ])),
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->before(function (Action $action, Machine $record): void {
                        if (self::hasActiveOrPlannedTasks($record)) {
                            Notification::make()
                                ->danger()
                                ->title('Nie można usunąć maszyny')
                                ->body('Ta maszyna jest przypisana do zadania zaplanowanego lub w trakcie realizacji.')
                                ->send();

                            $action->halt();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Usuń Maszynę')
                    ->modalDescription('Czy na pewno chcesz to zrobić?')
                    ->modalSubmitActionLabel('Tak, usuń')
                    ->action(function (Machine $record): void {
                        if (self::hasActiveOrPlannedTasks($record)) {
                            Notification::make()
                                ->danger()
                                ->title('Nie można usunąć maszyny')
                                ->body('Ta maszyna jest przypisana do zadania zaplanowanego lub w trakcie realizacji.')
                                ->send();

                            return;
                        }

                        $record->delete();
                    }),
            ])
            ->toolbarActions([
                //
            ]);
    }

    protected static function makeAssignedTasksView(Machine $machine): View
    {
        $assignments = TaskMachine::query()
            ->with(['task.field', 'user'])
            ->where(function ($query) use ($machine): void {
                $query->where('machine_id', $machine->getKey())
                    ->orWhere('tractor_id', $machine->getKey());
            })
            ->whereHas('task', function ($query): void {
                $query->whereIn('status', ['in_progress', 'planned']);
            })
            ->get()
            ->map(function (TaskMachine $assignment): array {
                $task = $assignment->task;
                $operatorName = $assignment->user
                    ? (trim(($assignment->user->first_name ?? '') . ' ' . ($assignment->user->last_name ?? '')) ?: ($assignment->user->email ?? 'Brak'))
                    : 'Brak';

                return [
                    'task_type' => $task?->type ?? 'Zadanie',
                    'field_name' => $task?->field?->name ?? 'Brak pola',
                    'status' => $task?->status ?? '',
                    'operator_name' => $operatorName,
                    'task_url' => $task ? TaskResource::getUrl('edit', ['record' => $task]) : null,
                ];
            })
            ->filter(fn (array $item): bool => filled($item['task_url']))
            ->values();

        return view('filament.modals.machine-assignments', [
            'assignments' => $assignments,
        ]);
    }

    protected static function hasActiveOrPlannedTasks(Machine $machine): bool
    {
        $blockedTaskStatuses = [
            'planned',
            'in_progress',
        ];

        return TaskMachine::query()
            ->where(function ($query) use ($machine): void {
                $query->where('machine_id', $machine->getKey())
                    ->orWhere('tractor_id', $machine->getKey());
            })
            ->whereHas('task', function ($query) use ($blockedTaskStatuses): void {
                $query->whereIn('status', $blockedTaskStatuses);
            })
            ->exists();
    }
}