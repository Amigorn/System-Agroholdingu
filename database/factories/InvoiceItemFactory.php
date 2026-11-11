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
        $quantity = fake()->numberBetween(1, 50); // Liczba całkowita zgodna z walidacją formularza
        $unitPrice = fake()->randomFloat(2, 50, 2500);

        return [
            // Przypisanie do istniejącej faktury lub utworzenie nowej
            'invoice_id' => Invoice::inRandomOrder()->first()?->id ?? Invoice::factory(),
            
            // Przypisanie do istniejącego produktu z magazynu lub utworzenie nowego
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => round($quantity * $unitPrice, 2), // Automatyczna wartość pozycji
            'notes' => fake()->optional()->sentence(),
        ];
    }
}