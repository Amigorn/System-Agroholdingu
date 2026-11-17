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
                    ->maxLength(255)
                    ->rules([
                        'required',
                        // Wyrażenie regularne dopasowujące format FV/coś/rok (np. FV/123/2026 lub FV/AB-45/2026)
                        'regex:/^FV\/[A-Za-z0-9\-\/]+\/\d{4}$/',
                    ])
                    ->validationMessages([
                        'required' => 'Numer faktury jest wymagany.',
                        'regex' => 'Nieprawidłowy format. Numer musi być w formacie np. FV/123/2026.',
                    ]),

                DatePicker::make('issue_date')
                    ->label('Data wystawienia')
                    ->required()
                    ->default(now())
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $items = $get('items') ?? [];
                        $issueDate = $state;

                        if (!$issueDate) {
                            return;
                        }

                        foreach ($items as $uuid => $item) {
                            if (!empty($item['product_id'])) {
                                $product = Product::find($item['product_id']);
                                $unitPrice = $product ? $product->getPriceForDate($issueDate) : 0;
                                $quantity = (int) ($item['quantity'] ?? 1);
                                
                                $itemTotal = number_format($quantity * $unitPrice, 2, '.', '');

                                $set("items.{$uuid}.unit_price", number_format($unitPrice, 2, '.', ''));
                                $set("items.{$uuid}.total", $itemTotal);
                            }
                        }

                        self::updateInvoiceTotal($set, $get);
                    }),

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
                    ->maxLength(255)
                    ->rules([
                        'required',
                        'string',
                        'min:2', // Nazwa kontrahenta musi mieć co najmniej 2 znaki
                        'regex:/[a-zA-ZĄĆĘŁŃÓŚŹŻąćęłńóśźż]/', // Wymaga przynajmniej jednej litery (chroni przed wpisaniem samych cyfr lub spacji)
                    ])
                    ->validationMessages([
                        'required' => 'Nazwa kontrahenta jest wymagana.',
                        'min' => 'Nazwa kontrahenta musi mieć co najmniej 2 znaki.',
                        'regex' => 'Nazwa kontrahenta musi zawierać poprawne litery.',
                    ]),

                // Główna wartość netto z wymuszonym formatem 2 miejsc po przecinku
                TextInput::make('total')
                    ->label('Całkowita wartość netto (PLN)')
                    ->required()
                    ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2, '.', ''))
                    ->readOnly()
                    ->dehydrated()
                    ->default('0.00'),

                Textarea::make('description')
                    ->label('Opis / Uwagi do faktury')
                    ->maxLength(500),

                // Repeater z pozycjami
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
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (!$state) {
                                    $set('unit_price', '0.00');
                                    $set('total', '0.00');
                                    self::updateInvoiceTotal($set, $get);
                                    return;
                                }

                                $issueDate = $get('../../issue_date') ?? now();
                                $product = Product::find($state);

                                $unitPrice = $product ? $product->getPriceForDate($issueDate) : 0;
                                $quantity = (int) ($get('quantity') ?? 1);
                                
                                $itemTotal = number_format($quantity * $unitPrice, 2, '.', '');

                                $set('unit_price', number_format($unitPrice, 2, '.', ''));
                                $set('total', $itemTotal);

                                self::updateInvoiceTotal($set, $get);
                            })
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
                                
                                $itemTotal = number_format($quantity * $unitPrice, 2, '.', '');
                                
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
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Pobieramy wybraną jednostkę oraz produkt
                                $unit = UnitOfMeasure::find($state);
                                $productId = $get('product_id');
                                $issueDate = $get('../../issue_date') ?? now();
                                $quantity = (int) ($get('quantity') ?? 1);

                                if ($productId) {
                                    $product = Product::find($productId);
                                    // Pobieramy bazową cenę z cennika
                                    $basePrice = $product ? $product->getPriceForDate($issueDate) : 0;

                                    $symbol = $unit ? strtolower($unit->symbol) : '';

                                    $unitPrice = $basePrice;
                                    if ($symbol === 'kg' || $symbol === 'g') {
                                        $unitPrice = $basePrice / 1000; 
                                    }

                                    $formattedPrice = number_format($unitPrice, 2, '.', '');
                                    $set('unit_price', $formattedPrice);

                                    $itemTotal = number_format($quantity * $unitPrice, 2, '.', '');
                                    $set('total', $itemTotal);
                                }

                                self::updateInvoiceTotal($set, $get);
                            })
                            ->columnSpan(['default' => 6, 'md' => 2]),

                        TextInput::make('unit_price')
                            ->label('Cena jedn. netto')
                            ->required()
                            ->inputMode('decimal')
                            ->rule('numeric')
                            ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2, '.', ''))
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $unitPrice = (float) str_replace(',', '.', $state);
                                $quantity = (int) $get('quantity');

                                $formattedPrice = number_format($unitPrice, 2, '.', '');
                                $set('unit_price', $formattedPrice);

                                $itemTotal = number_format($quantity * $unitPrice, 2, '.', '');
                                $set('total', $itemTotal);

                                self::updateInvoiceTotal($set, $get);
                            })
                            ->columnSpan(['default' => 6, 'md' => 2]),

                        // Wartość netto pozycji z wymuszonym formatem 2 miejsc po przecinku
                        TextInput::make('total')
                            ->label('Wartość netto')
                            ->required()
                            ->formatStateUsing(fn ($state) => number_format((float) ($state ?? 0), 2, '.', ''))
                            ->readOnly()
                            ->dehydrated()
                            ->default('0.00')
                            ->columnSpan(['default' => 6, 'md' => 3]),

                        Textarea::make('notes')
                            ->label('Uwagi do pozycji')
                            ->rows(2)
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(12)
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->addActionLabel('Dodaj pozycję')
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

        $set('../../total', number_format($total, 2, '.', ''));
    }
}