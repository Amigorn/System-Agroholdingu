<?php

namespace App\Filament\Resources\Fields\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CropsRelationManager extends RelationManager
{
    protected static string $relationship = 'crops';

    protected static ?string $title = 'Historia upraw (Płodozmian)';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nazwa uprawy / Odmiana')
                    ->placeholder('np. Pszenica ozima - Skagen')
                    ->required()
                    ->maxLength(255),

                Select::make('season_id')
                    ->label('Sezon gospodarczy')
                    ->relationship('season', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('description')
                    ->label('Uwagi do uprawy')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Uprawa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('season.name')
                    ->label('Sezon')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Uwagi')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label('Dodaj uprawę na tym polu'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}