<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class ProductPriceSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $units = UnitOfMeasure::all();

        if ($products->isEmpty()) {
            return;
        }

        // Pomocnicze wyszukanie jednostek po nazwie/symbolu (bez rozróżniania wielkości liter)
        $tonUnit = $units->first(function (UnitOfMeasure $unit): bool {
            $name = mb_strtolower((string) $unit->name, 'UTF-8');
            $symbol = mb_strtolower((string) $unit->symbol, 'UTF-8');

            return in_array($symbol, ['t', 'to'], true)
                || str_contains($name, 'ton');
        });

        $kgUnit = $units->first(function (UnitOfMeasure $unit): bool {
            $name = mb_strtolower((string) $unit->name, 'UTF-8');
            $symbol = mb_strtolower((string) $unit->symbol, 'UTF-8');

            return $symbol === 'kg' || str_contains($name, 'kilogram');
        });

        $literUnit = $units->first(function (UnitOfMeasure $unit): bool {
            $name = mb_strtolower((string) $unit->name, 'UTF-8');
            $symbol = mb_strtolower((string) $unit->symbol, 'UTF-8');

            return in_array($symbol, ['l', 'ltr'], true) || str_contains($name, 'litr');
        });

        foreach ($products as $product) {
            // Jeśli produkt ma już przypisaną jednostkę, używamy jej jako domyślnej
            $productUnitId = $product->unit_id
                ?? $product->unit_of_measure_id
                ?? $product->unit?->id
                ?? $product->unitOfMeasure?->id;

            $fallbackUnitId = match ($product->type) {
                'produkt_przetworzony' => $literUnit?->id ?? $kgUnit?->id ?? $units->random()?->id,
                default => $tonUnit?->id ?? $kgUnit?->id ?? $units->random()?->id,
            };

            $unitId = $productUnitId ?? $fallbackUnitId;

            // Bazowa cena zależna od typu produktu
            $basePrice = match ($product->type) {
                'produkt_przetworzony' => fake()->randomFloat(2, 400, 7000), // np. olej, mąka
                default => fake()->randomFloat(2, 800, 1800),             // np. plony (zboża)
            };

            // Definiujemy historyczne i obecne okresy obowiązywania cen z zamkniętymi datami końcowymi
            $pricePeriods = [
                [
                    'valid_from' => '2024-01-01',
                    'valid_until' => '2024-12-31',
                    'price' => round($basePrice * 0.85, 2), // Nieco tańsze w ubiegłym roku
                ],
                [
                    'valid_from' => '2025-01-01',
                    'valid_until' => '2025-12-31',
                    'price' => round($basePrice * 0.95, 2), // Średnia cena w zeszłym sezonie
                ],
                [
                    'valid_from' => '2026-01-01',
                    'valid_until' => '2026-12-31',
                    'price' => $basePrice,                  // Aktualna cena na bieżący rok
                ],
            ];

            foreach ($pricePeriods as $period) {
                ProductPrice::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'valid_from' => $period['valid_from'],
                    ],
                    [
                        'price' => $period['price'],
                        'unit_id' => $unitId,
                        'valid_until' => $period['valid_until'],
                    ]
                );
            }
        }
    }
}