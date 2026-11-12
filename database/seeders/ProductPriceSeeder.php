<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Seeder;

class ProductPriceSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
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
                        'valid_until' => $period['valid_until'],
                    ]
                );
            }
        }
    }
}