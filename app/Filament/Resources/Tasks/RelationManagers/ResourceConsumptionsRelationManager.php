<?php

namespace App\Filament\Resources\Tasks\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use App\Models\Resource;

class ResourceConsumptionsRelationManager extends RelationManager
{
    // Nazwa relacji zdefiniowana w modelu Task (public function resourceConsumptions())
    protected static string $relationship = 'resourceConsumptions';

    protected static ?string $title = 'Zużyte surowce i paliwo';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->status === 'completed';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('resource_id')
                    ->label('Surowiec')
                    ->options(Resource::query()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (!$state) {
                            $set('unit_symbol', '');
                            return;
                        }
                        $resource = Resource::with('unit')->find($state);
                        $symbol = $resource?->unit?->symbol ?? 'szt';
                        $set('unit_symbol', $symbol);
                    })
                    ->afterStateHydrated(function ($component, $state, callable $set) {
                        if ($state) {
                            $resource = Resource::with('unit')->find($state);
                            $symbol = $resource?->unit?->symbol ?? 'szt';
                            $set('unit_symbol', $symbol);
                        }
                    })
                    ->columnSpan(['default' => 12, 'md' => 6]),

                TextInput::make('unit_symbol')
                    ->label('Jednostka')
                    ->readOnly()
                    ->dehydrated(false)
                    ->placeholder('-')
                    ->columnSpan(['default' => 6, 'md' => 2]),

                TextInput::make('quantity')
                    ->label('Zużyta ilość')
                    ->numeric()
                    ->step('0.01')
                    ->rules(['numeric', 'min:0.01'])
                    ->required()
                    ->default(1)
                    ->columnSpan(['default' => 6, 'md' => 4]),
            ])
            ->columns(12);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('quantity')
            ->columns([
                TextColumn::make('resource.name')
                    ->label('Surowiec')
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Zużyta ilość')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('resource.unit.symbol')
                    ->label('Jednostka')
                    ->placeholder('-'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Dodaj zużyty surowiec')
                    ->modalHeading('Dodaj zużyty surowiec lub paliwo')
                    ->modalSubmitActionLabel('Dodaj')
                    ->createAnother(false), // Wyłącza przycisk "Utwórz i utwórz kolejny", zostawiając tylko "Dodaj" i "Anuluj"
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edytuj')
                    ->modalHeading('Edytuj surowiec')
                    ->modalSubmitActionLabel('Zapisz'),
                DeleteAction::make(),
            ]);
    }
}