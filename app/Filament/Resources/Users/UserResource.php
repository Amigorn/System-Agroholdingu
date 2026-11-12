<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope; // <-- Niezbędne do Soft Delete

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | UnitEnum | null $navigationGroup = 'Zarządzanie Gospodarstwem';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::User;

    // Polskie nazwy w menu bocznym
    protected static ?string $navigationLabel = 'Użytkownicy';

    protected static ?string $pluralModelLabel = 'Użytkownicy';
    protected static ?string $modelLabel = 'Użytkownika';

    // Tłumaczenie odmiany słowa dla automatycznych nagłówków Filamentu
    public static function getModelLabel(): string
    {
        return 'użytkownik';
    }

    public static function getPluralModelLabel(): string
    {
        return 'użytkownicy';
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    // Kluczowe dla Soft Delete: pozwala pobierać i obsługiwać usunięte rekordy przy routingu
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}'),
        ];
    }
}