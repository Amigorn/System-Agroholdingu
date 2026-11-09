<?php

namespace App\Filament\Resources\Resources;

use App\Filament\Resources\Resources\Pages\CreateResource;
use App\Filament\Resources\Resources\Pages\EditResource;
use App\Filament\Resources\Resources\Pages\ListResources;
use App\Filament\Resources\Resources\Schemas\ResourceForm;
use App\Filament\Resources\Resources\Tables\ResourcesTable;
use App\Models\Resource as ResourceModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ResourceResource extends Resource
{
    protected static ?string $model = ResourceModel::class;

    // Polskie etykiety w panelu
    protected static ?string $navigationLabel = 'Zasoby';
    protected static ?string $modelLabel = 'Zasób';
    protected static ?string $pluralModelLabel = 'Zasoby';

    // Grupa w menu bocznym oraz ikona
    protected static string|UnitEnum|null $navigationGroup = 'Magazyn i Zasoby';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::WrenchScrewdriver;

    public static function form(Schema $schema): Schema
    {
        return ResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResourcesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResources::route('/'),
            'create' => CreateResource::route('/create'),
            'edit' => EditResource::route('/{record}/edit'),
        ];
    }
}