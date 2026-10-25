<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UnitOfMeasure;

class ResourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Saletra Amonowa', 'YaraMila', 'Roundup 360', 'Chwastox']),
            'unit_id' => UnitOfMeasure::factory(),
        ];
    }
}