<?php

namespace App\Filament\Resources\Crops\Schemas;

use App\Models\Crop;
use App\Models\Season;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CropForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nazwa uprawy')
                    ->required()
                    ->maxLength(255),

                Select::make('season_id')
                    ->label('Sezon uprawowy')
                    ->relationship(
                        name: 'season',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->orderByDesc('id'),
                    )
                    ->default(fn (): ?int => Season::query()->orderByDesc('id')->value('id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(),

                Select::make('field_id')
                    ->label('Pole uprawne')
                    ->relationship('field', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->rule(function (Get $get, ?Model $record): Closure {
                        return function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                            if (blank($value)) {
                                return;
                            }

                            $query = Crop::query()
                                ->where('field_id', $value);

                            $seasonId = $get('season_id');
                            if (filled($seasonId)) {
                                $query->where('season_id', $seasonId);
                            }

                            if ($record?->getKey()) {
                                $query->whereKeyNot($record->getKey());
                            }

                            if ($query->exists()) {
                                $fail('To pole ma już przypisaną uprawę.');
                            }
                        };
                    }),

                DatePicker::make('sowing_date')
                    ->label('Data siewu'),

                Textarea::make('description')
                    ->label('Opis'),
            ]);
    }
}