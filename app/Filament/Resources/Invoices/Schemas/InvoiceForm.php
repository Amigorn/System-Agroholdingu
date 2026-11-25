<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Product;
use App\Models\UnitOfMeasure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
                                $unit = !empty($item['unit_id']) ? UnitOfMeasure::find($item['unit_id']) : null;
                                $basePrice = $product ? $product->getPriceForDate($issueDate) : 0;
                                $unitPrice = self::calculateUnitPrice($basePrice, $unit);
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
                                    $set('unit_id', null);
                                    $set('unit_price', '0.00');
                                    $set('total', '0.00');
                                    self::updateInvoiceTotal($set, $get);
                                    return;
                                }

                                $issueDate = $get('../../issue_date') ?? now();
                                $product = Product::find($state);

                                $defaultUnitId = $product?->unit_id
                                    ?? $product?->unit_of_measure_id
                                    ?? $product?->unit?->id
                                    ?? $product?->unitOfMeasure?->id;

                                $unit = null;
                                if ($defaultUnitId) {
                                    $set('unit_id', $defaultUnitId);
                                    $unit = UnitOfMeasure::find($defaultUnitId);
                                } elseif (!empty($get('unit_id'))) {
                                    $unit = UnitOfMeasure::find($get('unit_id'));
                                }

                                $basePrice = $product ? $product->getPriceForDate($issueDate) : 0;
                                $unitPrice = self::calculateUnitPrice($basePrice, $unit);
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
                                $unit = UnitOfMeasure::find($state);
                                $productId = $get('product_id');
                                $issueDate = $get('../../issue_date') ?? now();
                                $quantity = (int) ($get('quantity') ?? 1);

                                if ($productId) {
                                    $product = Product::find($productId);
                                    $basePrice = $product ? $product->getPriceForDate($issueDate) : 0;
                                    $unitPrice = self::calculateUnitPrice($basePrice, $unit);

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
                    ->extraAttributes([
                        'style' => 'font-family: Consolas, Menlo, Monaco, "Liberation Mono", "Courier New", monospace; font-size: 13px; font-weight: 500;',
                    ])
                    ->itemLabel(function (array $state, ?string $uuid, Repeater $component): ?string {
                        $keys = array_keys($component->getState());
                        $index = $uuid ? array_search($uuid, $keys) : false;
                        $position = $index !== false ? $index + 1 : 1;

                        $productName = !empty($state['product_id']) ? (Product::find($state['product_id'])?->name ?? 'Brak produktu') : null;
                        $unitSymbol = !empty($state['unit_id']) ? (UnitOfMeasure::find($state['unit_id'])?->symbol ?? '') : '';

                        if (!$productName && empty($state['quantity']) && empty($state['total'])) {
                            return "Pozycja #{$position}: Nowa pozycja";
                        }

                        $nbsp = "\u{00A0}";

                        $pad = function (string $value, int $width) use ($nbsp): string {
                            $len = mb_strlen($value, 'UTF-8');
                            if ($len >= $width) {
                                return $value . $nbsp;
                            }

                            return $value . str_repeat($nbsp, $width - $len);
                        };

                        $colProduct = $pad($productName ?? 'Brak produktu', 28);

                        $qtyText = 'Ilość: ' . trim(($state['quantity'] ?? 0) . ' ' . $unitSymbol);
                        $colQty = $pad($qtyText, 18);

                        $priceFormatted = number_format((float) str_replace(',', '.', (string) ($state['unit_price'] ?? 0)), 2, '.', '');
                        $colPrice = $pad("Cena: {$priceFormatted} PLN", 22);

                        $totalFormatted = number_format((float) str_replace(',', '.', (string) ($state['total'] ?? 0)), 2, '.', '');
                        $colTotal = "Wartość: {$totalFormatted} PLN";

                        $separator = str_repeat($nbsp, 3);

                        return "Pozycja #{$position}: " . implode($separator, [$colProduct, $colQty, $colPrice, $colTotal]);
                    })
                    ->deleteAction(fn ($action) => $action->after(fn (callable $set, callable $get) => self::updateInvoiceTotal($set, $get))),
            ]);
    }

    protected static function calculateUnitPrice(float $basePrice, ?UnitOfMeasure $unit): float
    {
        $symbol = $unit ? strtolower((string) $unit->symbol) : '';

        if ($symbol === 'kg' || $symbol === 'g') {
            return $basePrice / 1000;
        }

        return $basePrice;
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