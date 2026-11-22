<?php

namespace App\Filament\Resources\Crops\Tables;

use App\Filament\Resources\Crops\CropResource;
use App\Models\Season;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CropsTable
{
    protected static function resolveLatestSeasonId(): ?int
    {
        $seasonId = Season::query()
            ->orderByDesc('year')
            ->orderByDesc('id')
            ->value('id');

        return $seasonId !== null ? (int) $seasonId : null;
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->select('crops.*')
                ->with(['field', 'season'])
            )
            ->defaultSort('sowing_date', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('field.name')
                    ->label('Pole')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('field', function (Builder $q) use ($search): void {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('fields', 'crops.field_id', '=', 'fields.id')
                            ->orderBy('fields.name', $direction);
                    }),

                TextColumn::make('season.name')
                    ->label('Sezon uprawowy')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('season', function (Builder $q) use ($search): void {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('seasons', 'crops.season_id', '=', 'seasons.id')
                            ->orderBy('seasons.name', $direction);
                    }),

                TextColumn::make('sowing_date')
                    ->label('Data siewu')
                    ->date('d.m.Y')
                    ->placeholder('nie zasiane')
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
            ->filters([
                SelectFilter::make('season_id')
                    ->label('Sezon uprawowy')
                    ->options(fn (): array => Season::query()
                        ->orderByDesc('year')
                        ->orderByDesc('id')
                        ->pluck('name', 'id')
                        ->all())
                    ->default(fn (): ?int => static::resolveLatestSeasonId())
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => CropResource::getUrl('edit', [
                        'record' => $record,
                    ])),
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Usuń Uprawe')
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