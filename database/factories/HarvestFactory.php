<?php

namespace Database\Factories;

use App\Models\Crop;
use App\Models\Field;
use App\Models\UnitOfMeasure;
use App\Models\Product;
use App\Models\Harvest;
use Illuminate\Database\Eloquent\Factories\Factory;

class HarvestFactory extends Factory
{
    protected $model = Harvest::class;

    public function definition(): array
    {
        return [
            'date' => fake()->dateTimeBetween('-1 year', 'now'),
            'quantity' => fake()->randomFloat(2, 5, 100),
            'unit_id' => UnitOfMeasure::factory(),
            'crop_id' => Crop::factory(),
            'field_id' => Field::factory(),
            'product_id' => Product::factory(),
        ];
    }
}