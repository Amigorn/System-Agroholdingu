<?php

namespace App\Filament\Resources\Tasks\Tables;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Machine;
use App\Models\Resource;
use App\Models\ResourceConsumption;
use App\Models\TaskMachine;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as DbSchema;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with(['field', 'taskMachines'])
                ->orderByRaw("FIELD(status, 'in_progress', 'planned', 'completed')")
            )
            ->columns([
                TextColumn::make('type')
                    ->label('Zabieg')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('field.name')
                    ->label('Pole')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planned' => 'Zaplanowane',
                        'in_progress' => 'W realizacji',
                        'completed' => 'Zakończone',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'planned' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('planned_at')
                    ->label('Planowane rozpoczęcie')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),

                TextColumn::make('planned_end_at')
                    ->label('Planowane zakończenie')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),

                TextColumn::make('started_at')
                    ->label('Data rozpoczęcia')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('completed_at')
                    ->label('Data zakończenia')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
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
                        'in_progress' => 'W realizacji',
                        'completed' => 'Zakończone',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                Action::make('startTask')
                    ->label('Rozpocznij')
                    ->icon('heroicon-m-play')
                    ->button()
                    ->color('info')
                    ->visible(fn ($record) => $record->status === 'planned')
                    ->requiresConfirmation()
                    ->modalHeading('Rozpoczęcie zadania')
                    ->modalDescription('Czy na pewno chcesz zmienić status tego zadania na "W realizacji"?')
                    ->modalSubmitActionLabel('Tak, rozpocznij')
                    ->before(function ($record, Action $action) {
                        if ($record->taskMachines()->doesntExist()) {
                            Notification::make()
                                ->title('Brak zestawu roboczego')
                                ->body('Nie można rozpocząć zadania bez przypisanego zestawu roboczego.')
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    })
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'in_progress',
                            'started_at' => $record->started_at ?? now(),
                        ]);

                        foreach ($record->taskMachines as $tm) {
                            if ($tm->tractor_id) {
                                Machine::where('id', $tm->tractor_id)->update(['status' => 'in_use']);
                            }
                            if ($tm->machine_id) {
                                Machine::where('id', $tm->machine_id)->update(['status' => 'in_use']);
                            }
                        }

                        Notification::make()
                            ->title('Zadanie rozpoczęte, sprzęt oznaczony jako zajęty')
                            ->success()
                            ->send();
                    }),

                Action::make('completeTask')
                    ->label('Zakończ')
                    ->icon('heroicon-m-check-circle')
                    ->button()
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'in_progress')
                    ->modalHeading('Zakończenie zadania i rozliczenie zasobów')
                    ->modalDescription('Wprowadź zużyte paliwo oraz inne materiały. Spowoduje to zwolnienie przypisanych maszyn i operatorów.')
                    ->modalSubmitActionLabel('Zakończ zadanie')
                    ->form([
                        TextInput::make('fuel_liters')
                            ->label('Zużyte paliwo (litry)')
                            ->numeric()
                            ->step('0.01')
                            ->placeholder('Wpisz ilość zużytego paliwa ON')
                            ->rules(['required', 'numeric', 'min:0.01'])
                            ->validationMessages([
                                'required' => 'Pole zużyte paliwo jest wymagane.',
                                'numeric' => 'Wartość musi być liczbą.',
                                'min' => 'Ilość paliwa musi wynosić co najmniej 0.01 litra.',
                            ])
                            ->required()
                            ->columnSpanFull(),

                        Repeater::make('consumptions')
                            ->label('Inne zużyte surowce / materiały')
                            ->schema([
                                Select::make('resource_id')
                                    ->label('Surowiec')
                                    ->options(function () {
                                        return Resource::query()
                                            ->whereNot(function (Builder $q) {
                                                $q->whereRaw('LOWER(name) LIKE ?', ['%paliwo%'])
                                                    ->orWhereRaw('LOWER(name) LIKE ?', ['%olej napędowy%'])
                                                    ->orWhereRaw('LOWER(name) LIKE ?', ['%diesel%'])
                                                    ->orWhere('name', 'LIKE', '% ON')
                                                    ->orWhere('name', '=', 'ON');

                                                if (DbSchema::hasColumn('resources', 'type')) {
                                                    $q->orWhereRaw('LOWER(type) = ?', ['paliwo']);
                                                }
                                            })
                                            ->orderBy('name')
                                            ->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->validationMessages([
                                        'required' => 'Pole surowiec jest wymagane.',
                                    ])
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if (! $state) {
                                            $set('unit_symbol', '');

                                            return;
                                        }
                                        $resource = Resource::with('unit')->find($state);
                                        $symbol = $resource?->unit?->symbol ?? 'szt';
                                        $set('unit_symbol', $symbol);
                                    })
                                    ->columnSpan(['default' => 12, 'md' => 6]),

                                TextInput::make('quantity')
                                    ->label('Zużyta ilość')
                                    ->numeric()
                                    ->step('0.01')
                                    ->rules(['numeric', 'min:0.01'])
                                    ->validationMessages([
                                        'required' => 'Pole ilość jest wymagane.',
                                        'numeric' => 'Wartość musi być liczbą.',
                                        'min' => 'Zużyta ilość musi wynosić co najmniej 0.01.',
                                    ])
                                    ->required()
                                    ->default(1)
                                    ->columnSpan(['default' => 6, 'md' => 4]),

                                TextInput::make('unit_symbol')
                                    ->label('Jednostka')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->placeholder('-')
                                    ->columnSpan(['default' => 6, 'md' => 2]),
                            ])
                            ->columns(12)
                            ->addActionLabel('Dodaj kolejny surowiec')
                            ->columnSpanFull(),
                    ])
                    ->action(function ($record, array $data) {
                        if (! empty($data['fuel_liters']) && (float) $data['fuel_liters'] > 0) {
                            $fuelResource = Resource::where('name', 'LIKE', '%paliwo%')
                                ->orWhere('name', 'LIKE', '%ON%')
                                ->orWhere('name', 'LIKE', '%diesel%')
                                ->first();

                            if ($fuelResource) {
                                ResourceConsumption::create([
                                    'task_id' => $record->id,
                                    'resource_id' => $fuelResource->id,
                                    'quantity' => $data['fuel_liters'],
                                ]);
                            }
                        }

                        if (! empty($data['consumptions'])) {
                            foreach ($data['consumptions'] as $item) {
                                ResourceConsumption::create([
                                    'task_id' => $record->id,
                                    'resource_id' => $item['resource_id'],
                                    'quantity' => $item['quantity'],
                                ]);
                            }
                        }

                        $record->update([
                            'status' => 'completed',
                            'started_at' => $record->started_at ?? $record->planned_at ?? now(),
                            'completed_at' => now(),
                        ]);

                        $releaseMachineIfFree = function (?int $machineId) use ($record): void {
                            if (! $machineId) {
                                return;
                            }

                            $stillInUse = TaskMachine::query()
                                ->where(function (Builder $q) use ($machineId) {
                                    $q->where('tractor_id', $machineId)
                                        ->orWhere('machine_id', $machineId);
                                })
                                ->where('task_id', '!=', $record->id)
                                ->whereHas('task', fn (Builder $tq) => $tq->where('status', 'in_progress'))
                                ->exists();

                            if (! $stillInUse) {
                                Machine::whereKey($machineId)->update(['status' => 'available']);
                            }
                        };

                        foreach ($record->taskMachines as $tm) {
                            $releaseMachineIfFree($tm->tractor_id ? (int) $tm->tractor_id : null);
                            $releaseMachineIfFree($tm->machine_id ? (int) $tm->machine_id : null);
                        }

                        Notification::make()
                            ->title('Zadanie zakończone, paliwo i surowce rozliczone!')
                            ->success()
                            ->send();
                    }),

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