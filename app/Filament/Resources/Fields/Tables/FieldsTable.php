<?php

namespace App\Filament\Resources\Fields\Tables;

use App\Filament\Resources\Fields\FieldResource;
use App\Models\Crop;
use App\Models\Field;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FieldsTable
{
    protected static function hasLinkedRecords(Field $field): bool
    {
        $hasCrops = method_exists($field, 'crops')
            ? $field->crops()->exists()
            : (class_exists(Crop::class) && Crop::query()->where('field_id', $field->getKey())->exists());

        if ($hasCrops) {
            return true;
        }

        if (method_exists($field, 'tasks')) {
            return $field->tasks()->exists();
        }

        if (class_exists(Task::class)) {
            return Task::query()->where('field_id', $field->getKey())->exists();
        }

        return false;
    }

    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Nazwa')
                ->searchable()
                ->sortable(),
            TextColumn::make('area')
                ->label('Powierzchnia')
                ->suffix(' ha')
                ->sortable(),
            TextColumn::make('soil_type')
                ->label('Typ gleby')
                ->sortable(),
            TextColumn::make('location')
                ->label('Lokalizacja')
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
                SelectFilter::make('soil_type')
                    ->label('Typ Gleby')
                    ->options(fn () => Field::distinct()->pluck('soil_type', 'soil_type')->toArray())
                    ->native(false),
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => FieldResource::getUrl('edit', [
                        'record' => $record,
                    ])),
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Usuń Pole')
                    ->modalDescription('Czy na pewno chcesz to zrobić?')
                    ->modalSubmitActionLabel('Tak, usuń')
                    ->before(function (Action $action, Field $record): void {
                        if (static::hasLinkedRecords($record)) {
                            Notification::make()
                                ->danger()
                                ->title('Nie można usunąć pola')
                                ->body('To pole ma przypisane uprawy lub zadania.')
                                ->send();

                            $action->halt();
                        }
                    })
                    ->action(fn (Field $record) => $record->delete()),
            ])
            ->toolbarActions([
                //
            ]);
    }
}