<?php

namespace App\Filament\Resources\Machines\Tables;

use App\Filament\Resources\Machines\MachineResource;
use App\Models\Machine;
use App\Models\TaskMachine;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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