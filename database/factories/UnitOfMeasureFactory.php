<?php

namespace Database\Factories;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitOfMeasure>
 */
class UnitOfMeasureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Kilogram', 'Litr', 'Tona']),
            'symbol' => fn (array $attr) => match($attr['name']) {
                'Kilogram' => 'kg',
                'Litr' => 'l',
                'Tona' => 't',
            },
        ];
    }
}