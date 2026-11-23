<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Numer faktury')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('issue_date')
                    ->label('Data wystawienia')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Typ dokumentu')
                    ->badge()
                    ->colors([
                        'danger' => 'purchase',    // Zakup na czerwono
                        'success' => 'sale',       // Sprzedaż na zielono
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'purchase' => 'Zakup (Koszt)',
                        'sale' => 'Sprzedaż (Przychód)',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contractor')
                    ->label('Kontrahent')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total')
                    ->alignRight()
                    ->label('Wartość')
                    ->money('PLN')
                    ->sortable(),

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
                SelectFilter::make('type')
                    ->label('Typ dokumentu')
                    ->options([
                        'purchase' => 'Zakup',
                        'sale' => 'Sprzedaż',
                    ])
                    ->native(false),
                Filter::make('amount_range')
                    ->label('Wartość faktury')
                    ->form([
                        TextInput::make('from')
                            ->label('Kwota od (PLN)')
                            ->numeric()
                            ->placeholder('np. 1000'),
                        TextInput::make('to')
                            ->label('Kwota do (PLN)')
                            ->numeric()
                            ->placeholder('np. 50000'),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $value): Builder => $query->where('total', '>=', $value),
                            )
                            ->when(
                                $data['to'] ?? null,
                                fn (Builder $query, $value): Builder => $query->where('total', '<=', $value),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = 'Kwota od: ' . number_format($data['from'], 2, ',', ' ') . ' PLN';
                        }

                        if ($data['to'] ?? null) {
                            $indicators[] = 'Kwota do: ' . number_format($data['to'], 2, ',', ' ') . ' PLN';
                        }

                        return $indicators;
                    })
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->label(false)
                    ->color('warning')
                    ->url(fn ($record) => InvoiceResource::getUrl('edit', [
                    'record' => $record,
                ])),
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->label(false)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Usuń fakturę')
                    ->modalDescription('Czy na pewno chcesz to zrobić?')
                    ->modalSubmitActionLabel('Tak, usuń')
                    ->action(fn ($record) => $record->delete()),
            ])
            ->toolbarActions([
                    //
            ]);
    }
}