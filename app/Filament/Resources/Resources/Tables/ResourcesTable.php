<?php

namespace App\Filament\Resources\Resources\Tables;

use App\Filament\Resources\Resources\ResourceResource;
use App\Models\Resource;
use App\Models\ResourceConsumption;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class ResourcesTable
{
    protected static function shouldBlockResourceDeletion($record): bool
    {
        $resourceId = $record->getKey();

        $hasConsumptions = class_exists(ResourceConsumption::class)
            && Schema::hasTable('resource_consumptions')
            && ResourceConsumption::query()->where('resource_id', $resourceId)->exists();

        if ($hasConsumptions) {
            Notification::make()
                ->danger()
                ->title('Nie można usunąć zasobu')
                ->body('Ten zasób ma już zarejestrowane zużycie w zadaniach.')
                ->send();

            return true;
        }

        return false;
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa zasobu')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Typ zasobu')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('unit.name')
                    ->label('Jednostka miary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Zaktualizowano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Typ zasobu')
                    ->options(fn () => Resource::distinct()->pluck('type', 'type')->toArray())
                    ->native(false),
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => ResourceResource::getUrl('edit', [
                        'record' => $record,
                    ])),

                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Usuń Zasób')
                    ->modalDescription('Czy na pewno chcesz to zrobić?')
                    ->modalSubmitActionLabel('Tak, usuń')
                    ->before(function ($record, Action $action): void {
                        if (static::shouldBlockResourceDeletion($record)) {
                            $action->halt();
                        }
                    })
                    ->action(function ($record, Action $action): void {
                        if (static::shouldBlockResourceDeletion($record)) {
                            $action->halt();

                            return;
                        }

                        $record->delete();
                    }),
            ])
            ->toolbarActions([
                    //
            ]);
    }
}