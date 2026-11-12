<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPriceFactory extends Factory
{
    protected $model = ProductPrice::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            'price' => $this->faker->randomFloat(2, 50, 3500), // Cena w przedziale od 50 do 3500 PLN
            'valid_from' => now()->startOfYear(),
            'valid_until' => null, // Bezterminowo
        ];
    }
}