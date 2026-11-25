<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPriceFactory extends Factory
{
    protected $model = ProductPrice::class;

    public function definition(): array
    {
        $unitId = UnitOfMeasure::inRandomOrder()->value('id');

        if (! $unitId && class_exists(\Database\Factories\UnitOfMeasureFactory::class)) {
            $unitId = UnitOfMeasure::factory();
        }

        return [
            'product_id' => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            'price' => $this->faker->randomFloat(2, 50, 3500), // Cena w przedziale od 50 do 3500 PLN
            'unit_id' => $unitId,
            'valid_from' => now()->startOfYear(),
            'valid_until' => null, // Bezterminowo
        ];
    }
}