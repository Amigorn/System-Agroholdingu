<?php

namespace Database\Factories;

use App\Models\Crop;
use App\Models\Field;
use App\Models\UnitOfMeasure;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class HarvestFactory extends Factory
{
    public function definition(): array
{
    return [
        'data_zbioru' => fake()->dateTimeBetween('-1 year', 'now'),
        'ilosc' => fake()->randomFloat(2, 5, 100),
        'jednostka_id' => UnitOfMeasure::factory(),
        'uprawa_id' => Crop::factory(),
        'pole_id' => Field::factory(),
        'produkt_id' => Product::factory(),
    ];
}
}