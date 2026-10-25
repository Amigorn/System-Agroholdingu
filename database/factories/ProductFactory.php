<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UnitOfMeasure;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Pszenica konsumpcyjna', 'Rzepak przemysłowy', 'Mleko surowe']),
            'type' => fake()->randomElement(['Zboża', 'Oleiste', 'Nabiał']),
            'unit_id' => UnitOfMeasure::factory(),
        ];
    }
}