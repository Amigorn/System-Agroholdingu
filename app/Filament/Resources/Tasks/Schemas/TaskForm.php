<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Models\Crop;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TaskForm
{
    protected static function extractYear(mixed $date): ?int
    {
        if (blank($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->year;
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function resolveCropIdForFieldAndYear(mixed $fieldId, ?int $year): ?int
    {
        if (blank($fieldId)) {
            return null;
        }

        if ($year !== null) {
            $cropId = Crop::query()
                ->leftJoin('seasons', 'crops.season_id', '=', 'seasons.id')
                ->where('crops.field_id', $fieldId)
                ->where('seasons.year', $year)
                ->orderByDesc('crops.id')
                ->value('crops.id');

            if ($cropId !== null) {
                return (int) $cropId;
            }
        }

        $latestCropId = Crop::query()
            ->leftJoin('seasons', 'crops.season_id', '=', 'seasons.id')
            ->where('crops.field_id', $fieldId)
            ->orderByDesc('seasons.year')
            ->orderByDesc('crops.season_id')
            ->orderByDesc('crops.id')
            ->value('crops.id');

        return $latestCropId !== null ? (int) $latestCropId : null;
    }

    protected static function syncCropSelection(Get $get, Set $set): void
    {
        $isSowing = mb_strtolower(trim((string) ($get('type') ?? ''))) === 'siew';
        $fieldId = $get('field_id');

        if (! $isSowing || blank($fieldId)) {
            $set('crop_id', null);

            return;
        }

        $status = $get('status');
        $taskDate = $get('completed_at') ?? $get('started_at') ?? $get('planned_at');
        $year = static::extractYear($taskDate);

        if ($status === 'completed') {
            $set('crop_id', static::resolveCropIdForFieldAndYear($fieldId, $year));

            return;
        }

        $set('crop_id', static::resolveCropIdForFieldAndYear($fieldId, null));
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('type')
                ->label('Rodzaj zabiegu')
                ->datalist([
                    'Nawożenie',
                    'Oprysk',
                    'Orka',
                    'Siew',
                ])
                ->live()
                ->afterStateUpdated(fn (Get $get, Set $set) => static::syncCropSelection($get, $set))
                ->required(),

            Select::make('field_id')
                ->label('Pole uprawne')
                ->relationship('field', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(fn (Get $get, Set $set) => static::syncCropSelection($get, $set)),

            Select::make('crop_id')
                ->label('Uprawa na polu')
                ->visible(function (Get $get): bool {
                    $isSowing = mb_strtolower(trim((string) ($get('type') ?? ''))) === 'siew';

                    return $isSowing && (filled($get('field_id')) || filled($get('crop_id')) || $get('status') === 'completed');
                })
                ->afterStateHydrated(function ($state, Get $get, Set $set): void {
                    if (filled($state)) {
                        return;
                    }

                    $isSowing = mb_strtolower(trim((string) ($get('type') ?? ''))) === 'siew';
                    $fieldId = $get('field_id');

                    if (! $isSowing || blank($fieldId)) {
                        return;
                    }

                    $taskDate = $get('completed_at') ?? $get('started_at') ?? $get('planned_at');
                    $year = static::extractYear($taskDate);

                    $resolvedId = static::resolveCropIdForFieldAndYear($fieldId, $year);
                    if ($resolvedId !== null) {
                        $set('crop_id', $resolvedId);
                    }
                })
                ->options(function (Get $get): array {
                    $fieldId = $get('field_id');
                    $currentCropId = $get('crop_id');
                    $options = [];

                    if (filled($fieldId)) {
                        $seasonId = null;

                        if (filled($currentCropId)) {
                            $seasonId = Crop::query()->whereKey((int) $currentCropId)->value('season_id');
                        }

                        if ($seasonId === null) {
                            $taskDate = $get('completed_at') ?? $get('started_at') ?? $get('planned_at');
                            $year = static::extractYear($taskDate);

                            if ($year !== null) {
                                $seasonId = Crop::query()
                                    ->leftJoin('seasons', 'crops.season_id', '=', 'seasons.id')
                                    ->where('crops.field_id', $fieldId)
                                    ->where('seasons.year', $year)
                                    ->orderByDesc('crops.id')
                                    ->value('crops.season_id');
                            }
                        }

                        if ($seasonId === null) {
                            $seasonId = Crop::query()
                                ->leftJoin('seasons', 'crops.season_id', '=', 'seasons.id')
                                ->where('crops.field_id', $fieldId)
                                ->orderByDesc('seasons.year')
                                ->orderByDesc('crops.season_id')
                                ->orderByDesc('crops.id')
                                ->value('crops.season_id');
                        }

                        $query = Crop::query()
                            ->with('season')
                            ->where('field_id', $fieldId)
                            ->orderByDesc('id');

                        if ($seasonId !== null) {
                            $query->where('season_id', $seasonId);
                        } else {
                            $query->whereNull('season_id');
                        }

                        foreach ($query->get() as $crop) {
                            $seasonName = $crop->season?->name ?? null;
                            $seasonLabel = $seasonName ? " ({$seasonName})" : '';
                            $options[(int) $crop->id] = "{$crop->name}{$seasonLabel}";
                        }
                    }

                    if (filled($currentCropId) && ! array_key_exists((int) $currentCropId, $options)) {
                        $existingCrop = Crop::with('season')->find((int) $currentCropId);
                        if ($existingCrop) {
                            $seasonName = $existingCrop->season?->name ?? null;
                            $seasonLabel = $seasonName ? " ({$seasonName})" : '';
                            $options[(int) $existingCrop->id] = "{$existingCrop->name}{$seasonLabel}";
                        }
                    }

                    return $options;
                })
                ->disabled()
                ->dehydrated()
                ->helperText(function (Get $get): string {
                    if ($get('status') === 'completed' && blank($get('crop_id'))) {
                        return 'Uprawa zebrana (brak przypisanej uprawy w tym sezonie na wybranym polu).';
                    }

                    return '';
                }),

            Select::make('status')
                ->label('Status')
                ->default('planned')
                ->options([
                    'planned' => 'Zaplanowane',
                    'in_progress' => 'W realizacji',
                    'completed' => 'Zakończone',
                ])
                ->live()
                ->required(),

            DateTimePicker::make('planned_at')
                ->label('Planowane rozpoczęcie')
                ->seconds(false)
                ->required(),

            DateTimePicker::make('planned_end_at')
                ->seconds(false)
                ->label('Planowane zakończenie')
                ->required(),

            DateTimePicker::make('started_at')
                ->seconds(false)
                ->label('Data rozpoczęcia'),

            DateTimePicker::make('completed_at')
                ->seconds(false)
                ->label('Data zakończenia'),

            Textarea::make('description')
                ->label('Opis zadania')
                ->columnSpanFull(),
        ]);
    }
}