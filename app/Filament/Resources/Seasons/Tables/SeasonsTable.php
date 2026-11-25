<?php

namespace App\Filament\Resources\Seasons\Tables;

use App\Filament\Resources\Seasons\SeasonResource;
use App\Models\Crop;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SeasonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('year')
                    ->label('Rok')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Nazwa sezonu')
                    ->searchable()
                    ->sortable(),

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
            ->defaultSort('year', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => SeasonResource::getUrl('edit', [
                        'record' => $record,
                    ])),
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Usuń Sezon')
                    ->modalDescription('Czy na pewno chcesz to zrobić?')
                    ->modalSubmitActionLabel('Tak, usuń')
                    ->before(function (Action $action, Model $record): void {
                        $hasCrops = Crop::query()
                            ->where('season_id', $record->getKey())
                            ->exists();

                        if ($hasCrops) {
                            Notification::make()
                                ->danger()
                                ->title('Nie można usunąć sezonu')
                                ->body('Ten sezon ma już przypisane uprawy.')
                                ->send();

                            $action->halt();
                        }
                    })
                    ->action(fn ($record) => $record->delete()),
            ])
            ->toolbarActions([
                //
            ]);
    }
}