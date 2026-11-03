<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('numer')
                    ->label('Numer faktury')
                    ->placeholder('np. FV/123/2026')
                    ->required()
                    ->maxLength(255),

                DatePicker::make('data_wystawienia')
                    ->label('Data wystawienia')
                    ->required(),

                Select::make('typ')
                    ->label('Typ dokumentu')
                    ->options([
                        'zakup' => 'Zakup (Koszt)',
                        'sprzedaz' => 'Sprzedaż (Przychód)',
                    ])
                    ->required(),

                TextInput::make('kontrahent')
                    ->label('Kontrahent')
                    ->required()
                    ->maxLength(255),

                // Całkowita wartość wyliczana automatycznie ze sumy pozycji
                TextInput::make('wartosc')
                    ->label('Całkowita wartość netto (PLN)')
                    ->required()
                    ->numeric()
                    ->readOnly()
                    ->dehydrated()
                    ->default(0.0),

                Textarea::make('opis')
                    ->label('Opis / Uwagi')
                    ->columnSpanFull(),

                // Repeater z poprawnie zamkniętymi nawiasami i automatycznym sumowaniem
                Repeater::make('items')
                    ->relationship('items')
                    ->label('Pozycje faktury')
                    ->schema([
                        Select::make('produkt_id')
                            ->label('Produkt')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(2),

                        TextInput::make('ilosc')
                            ->label('Ilość')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $ilosc = (float) $state;
                                $cena = (float) $get('cena_jednostkowa');
                                $wartoscPozycji = round($ilosc * $cena, 2); // Poprawiono nawias
                                
                                $set('wartosc', $wartoscPozycji);
                                self::updateInvoiceTotal($set, $get);
                            }),

                        TextInput::make('cena_jednostkowa')
                            ->label('Cena jedn. netto (PLN)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $cena = (float) $state;
                                $ilosc = (float) $get('ilosc');
                                $wartoscPozycji = round($ilosc * $cena, 2); // Poprawiono nawias
                                
                                $set('wartosc', $wartoscPozycji);
                                self::updateInvoiceTotal($set, $get);
                            }),

                        TextInput::make('wartosc')
                            ->label('Wartość netto (PLN)')
                            ->numeric()
                            ->required()
                            ->readOnly()
                            ->dehydrated()
                            ->default(0.0),

                        Textarea::make('uwagi')
                            ->label('Uwagi do pozycji')
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->columnSpanFull()
                    ->addActionLabel('Dodaj pozycję')
                    ->collapsible()
                    ->collapsed()
                    ->itemLabel(fn (array $state): ?string => 
                        isset($state['ilosc'], $state['wartosc']) 
                            ? "Ilość: {$state['ilosc']} szt. | Wartość: {$state['wartosc']} PLN" 
                            : 'Nowa pozycja'
                    )
                    ->deleteAction(fn ($action) => $action->after(fn (callable $set, callable $get) => self::updateInvoiceTotal($set, $get))),
            ]);
    }

    protected static function updateInvoiceTotal(callable $set, callable $get): void
    {
        $items = $get('../../items') ?? [];
        
        $total = 0;
        foreach ($items as $item) {
            $total += (float) ($item['wartosc'] ?? 0);
        }

        $set('../../wartosc', round($total, 2));
    }
}