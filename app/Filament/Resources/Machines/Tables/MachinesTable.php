<?php

namespace App\Filament\Resources\Machines\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use App\Filament\Resources\Machines\MachineResource;
use app\Models\Machine;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
class MachinesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Nazwa')
                ->sortable()
                ->searchable(),
            TextColumn::make('type')
                ->label('Typ')
                ->sortable(),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Dostępny',
                        'in_use' => 'W użyciu',
                        'under_repair' => 'W naprawie',
                        default => $state,
                    })
                ->color(fn (string $state): string => match ($state) {
                    'available' => 'success',
                    'in_use' => 'warning',
                    'under_repair' => 'danger',
                    default => 'gray',
                }),
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
                SelectFilter::make('status')
                    ->label('Status maszyny')
                    ->options([
                        'available' => 'Dostępny',
                        'in_use' => 'W użyciu',
                        'under_repair' => 'W naprawie',
                    ])
                    ->native(false),

                SelectFilter::make('type')
                    ->label('Typ maszyny')
                    // Dynamiczne pobranie unikalnych typów bezpośrednio z bazy danych
                    ->options(fn () => Machine::distinct()->pluck('type', 'type')->toArray())
                    ->native(false),
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => MachineResource::getUrl('edit', [
                    'record' => $record,
                ])),
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Usuń Maszyne')
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
