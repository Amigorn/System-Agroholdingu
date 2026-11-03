<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $ilosc = fake()->randomFloat(2, 1, 50);
        $cenaJednostkowa = fake()->randomFloat(2, 50, 2500);

        return [
            // Przypisanie do istniejącej faktury lub utworzenie nowej
            'faktura_id' => Invoice::inRandomOrder()->first()?->id ?? Invoice::factory(),
            
            // Przypisanie do istniejącego produktu z magazynu lub utworzenie nowego
            'produkt_id' => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            
            'ilosc' => $ilosc,
            'cena_jednostkowa' => $cenaJednostkowa,
            'wartosc' => round($ilosc * $cenaJednostkowa, 2), // Automatyczna wartość pozycji
            'uwagi' => fake()->optional()->sentence(),
        ];
    }
}