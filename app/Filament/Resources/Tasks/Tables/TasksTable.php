<?php

namespace App\Filament\Resources\Tasks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use App\Filament\Resources\Tasks\TaskResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Machine;

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
                    ->dateTime('Y-m-d')
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label('Data zakończenia')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
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
                    ->native(false),
            ])
            ->recordActions([
                // Przycisk "Rozpocznij" - widoczny tylko dla statusu 'planned'
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
                    ->action(function ($record) {
                        // 1. Zmieniamy status zadania
                        $record->update([
                            'status' => 'in_progress',
                        ]);

                        // 2. Aktualizujemy status powiązanych maszyn i ciągników na "w użyciu"
                        foreach ($record->taskMachines as $tm) {
                            if ($tm->tractor_id) {
                                Machine::where('id', $tm->tractor_id)->update(['status' => 'working']);
                            }
                            if ($tm->machine_id) {
                                Machine::where('id', $tm->machine_id)->update(['status' => 'working']);
                            }
                        }
                        
                        Notification::make()
                            ->title('Zadanie rozpoczęte, sprzęt oznaczony jako zajęty')
                            ->success()
                            ->send();
                    }),

                // Przycisk "Zakończ"
                Action::make('completeTask')
                    ->label('Zakończ')
                    ->icon('heroicon-m-check-circle')
                    ->button()
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'in_progress')
                    ->requiresConfirmation()
                    ->modalHeading('Zakończenie zadania')
                    ->modalDescription('Czy zadanie zostało ukończone? Spowoduje to zwolnienie przypisanych maszyn i operatorów.')
                    ->modalSubmitActionLabel('Tak, zakończ')
                    ->action(function ($record) {
                        // 1. Zmieniamy status zadania i ustawiamy datę
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);

                        // 2. Zwalniamy sprzęt - zmieniamy status maszyn z powrotem na "dostępna"
                        foreach ($record->taskMachines as $tm) {
                            if ($tm->tractor_id) {
                                Machine::where('id', $tm->tractor_id)->update(['status' => 'available']);
                            }
                            if ($tm->machine_id) {
                                Machine::where('id', $tm->machine_id)->update(['status' => 'available']);
                            }
                        }

                        Notification::make()
                            ->title('Zadanie zakończone, sprzęt zwolniony')
                            ->success()
                            ->send();
                    }),

                // Akcja edycji
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => TaskResource::getUrl('edit', [
                        'record' => $record,
                    ])),

                // Akcja usuwania
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