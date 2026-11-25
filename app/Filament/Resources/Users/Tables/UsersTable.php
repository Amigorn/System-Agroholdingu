<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\UserResource;
use App\Models\Task;
use App\Models\TaskMachine;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    protected static function hasLinkedRecords(User $user): bool
    {
        $userId = $user->getKey();

        $inTaskMachines = TaskMachine::query()
            ->where('user_id', $userId)
            ->exists();

        if ($inTaskMachines) {
            return true;
        }

        return Task::query()
            ->where('user_id', $userId)
            ->exists();
    }

    protected static function blockIfRestricted(Action $action, User $record, bool $isForceDelete = false): bool
    {
        $currentUserId = auth()->id();

        if ($currentUserId !== null && (string) $record->getKey() === (string) $currentUserId) {
            Notification::make()
                ->danger()
                ->title($isForceDelete ? 'Nie można trwale usunąć konta' : 'Nie można usunąć konta')
                ->body('Nie możesz usunąć aktualnie zalogowanego użytkownika.')
                ->send();

            $action->halt();

            return true;
        }

        if (static::hasLinkedRecords($record)) {
            Notification::make()
                ->danger()
                ->title($isForceDelete ? 'Nie można trwale usunąć użytkownika' : 'Nie można usunąć użytkownika')
                ->body('Ten użytkownik jest przypisany do zestawu roboczego w zadaniu.')
                ->send();

            $action->halt();

            return true;
        }

        return false;
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('Imię')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('last_name')
                    ->label('Nazwisko')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Adres email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('role')
                    ->label('Rola')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'worker' => 'success',
                        'warehouseman' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Administrator',
                        'manager' => 'Kierownik',
                        'worker' => 'Pracownik',
                        'warehouseman' => 'Magazynier',
                        default => $state,
                    })
                    ->searchable(),

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
                SelectFilter::make('role')
                    ->label('Rola')
                    ->options([
                        'admin' => 'Administrator',
                        'manager' => 'Kierownik',
                        'worker' => 'Pracownik',
                        'warehouseman' => 'Magazynier',
                    ])
                    ->native(false),

                TrashedFilter::make()->label('Status usunięcia'),
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => UserResource::getUrl('edit', [
                        'record' => $record,
                    ])),

                // Standardowe miękkie usunięcie (przeniesienie do kosza)
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Usuń Użytkownika')
                    ->modalDescription('Czy na pewno chcesz przenieść tego użytkownika do kosza?')
                    ->modalSubmitActionLabel('Tak, usuń')
                    ->visible(fn ($record) => ! $record->trashed())
                    ->before(fn (Action $action, User $record) => static::blockIfRestricted($action, $record, false))
                    ->action(function (Action $action, User $record): void {
                        if (static::blockIfRestricted($action, $record, false)) {
                            return;
                        }

                        $record->delete();
                    }),

                // Przywrócenie z kosza gotową akcją Filamenta
                RestoreAction::make()
                    ->icon('heroicon-m-arrow-uturn-left')
                    ->button()
                    ->label(false),

                // Trwałe usunięcie z bazy
                ForceDeleteAction::make()
                    ->icon('heroicon-m-x-circle')
                    ->button()
                    ->label(false)
                    ->before(fn (Action $action, User $record) => static::blockIfRestricted($action, $record, true))
                    ->action(function (Action $action, User $record): void {
                        if (static::blockIfRestricted($action, $record, true)) {
                            return;
                        }

                        $record->forceDelete();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    RestoreBulkAction::make()->label('Przywróć wybrane'),
                    ForceDeleteBulkAction::make()->label('Usuń trwale wybrane'),
                ])->label('Akcje masowe'),
            ]);
    }
}