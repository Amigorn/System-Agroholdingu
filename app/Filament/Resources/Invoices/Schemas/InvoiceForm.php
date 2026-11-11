<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use App\Models\Product;
use App\Models\UnitOfMeasure;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label('Numer faktury')
                    ->placeholder('np. FV/123/2026')
                    ->required()
                    ->maxLength(255),

                DatePicker::make('issue_date')
                    ->label('Data wystawienia')
                    ->required()
                    ->default(now()),

                Select::make('type')
                    ->label('Typ dokumentu')
                    ->options([
                        'purchase' => 'Zakup (Koszt)',
                        'sale' => 'Sprzedaż (Przychód)',
                    ])
                    ->required(),

                TextInput::make('contractor')
                    ->label('Kontrahent')
                    ->required()
                    ->maxLength(255),

                // Całkowita wartość wyliczana automatycznie ze sumy pozycji
                TextInput::make('total')
                    ->label('Całkowita wartość netto (PLN)')
                    ->required()
                    ->numeric()
                    ->readOnly()
                    ->dehydrated()
                    ->default(0.0),

                Textarea::make('description')
                    ->label('Opis / Uwagi do faktury')
                    ->maxLength(500)
                    ->columnSpanFull(),

                // Kompaktowy Repeater z pozycjami
                Repeater::make('items')
                    ->relationship('items')
                    ->label('Pozycje faktury')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produkt')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(['default' => 12, 'md' => 3]),

                        TextInput::make('quantity')
                            ->label('Ilość')
                            ->numeric()
                            ->integer()
                            ->step(1)
                            ->rules(['integer', 'min:1'])
                            ->required()
                            ->default(1)
                            ->extraInputAttributes([
                                'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57',
                            ])
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $quantity = (int) $state;
                                $unitPrice = (float) $get('unit_price');
                                $itemTotal = round($quantity * $unitPrice, 2);
                                
                                $set('total', $itemTotal);
                                self::updateInvoiceTotal($set, $get);
                            })
                            ->columnSpan(['default' => 6, 'md' => 2]),

                        Select::make('unit_id')
                            ->label('Jednostka')
                            ->relationship('unit', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(['default' => 6, 'md' => 2]),

                        TextInput::make('unit_price')
                            ->label('Cena jedn. netto')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $unitPrice = (float) $state;
                                $quantity = (int) $get('quantity');
                                $itemTotal = round($quantity * $unitPrice, 2);
                                
                                $set('total', $itemTotal);
                                self::updateInvoiceTotal($set, $get);
                            })
                            ->columnSpan(['default' => 6, 'md' => 2]),

                        TextInput::make('total')
                            ->label('Wartość netto')
                            ->numeric()
                            ->required()
                            ->readOnly()
                            ->dehydrated()
                            ->default(0.0)
                            ->columnSpan(['default' => 6, 'md' => 3]),

                        Textarea::make('notes')
                            ->label('Uwagi do pozycji')
                            ->rows(2)
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(12)
                    ->columnSpanFull()
                    ->addActionLabel('Dodaj pozycję')
                    ->collapsible()
                    ->collapsed()
                    ->itemLabel(function (array $state, ?string $uuid, Repeater $component): ?string {
                        $keys = array_keys($component->getState());
                        $index = $uuid ? array_search($uuid, $keys) : false;
                        $position = $index !== false ? $index + 1 : 1;

                        $productName = !empty($state['product_id']) ? Product::find($state['product_id'])?->name : null;
                        $unitSymbol = !empty($state['unit_id']) ? UnitOfMeasure::find($state['unit_id'])?->symbol : '';

                        if (!$productName && empty($state['quantity']) && empty($state['total'])) {
                            return "Pozycja #{$position}: Nowa pozycja";
                        }

                        $parts = [];
                        if ($productName) {
                            $parts[] = $productName;
                        }
                        if (isset($state['quantity'])) {
                            $parts[] = "Ilość: {$state['quantity']} {$unitSymbol}";
                        }
                        if (isset($state['unit_price'])) {
                            $parts[] = "Cena: {$state['unit_price']} PLN";
                        }
                        if (isset($state['total'])) {
                            $parts[] = "Wartość pozycji: {$state['total']} PLN";
                        }

                        $details = count($parts) > 0 ? implode(' | ', $parts) : 'Nowa pozycja';

                        return "Pozycja #{$position}: {$details}";
                    })
                    ->deleteAction(fn ($action) => $action->after(fn (callable $set, callable $get) => self::updateInvoiceTotal($set, $get))),
            ]);
    }

    protected static function updateInvoiceTotal(callable $set, callable $get): void
    {
        $items = $get('../../items') ?? [];
        
        $total = 0;
        foreach ($items as $item) {
            $total += (float) ($item['total'] ?? 0);
        }

        $set('../../total', round($total, 2));
    }
}