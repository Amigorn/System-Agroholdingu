<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UnitOfMeasure;

class ResourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true), // Unikalna nazwa testowa
            'type' => 'Inne',
            'unit_id' => UnitOfMeasure::inRandomOrder()->first()?->id ?? UnitOfMeasure::factory(),
        ];
    }
}