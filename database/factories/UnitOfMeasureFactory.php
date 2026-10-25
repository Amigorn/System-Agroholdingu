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
        'nazwa' => fake()->randomElement(['Kilogram', 'Litr', 'Tona']),
        'skrot' => fn (array $attr) => match($attr['nazwa']) {
            'Kilogram' => 'kg',
            'Litr' => 'l',
            'Tona' => 't',
        },
    ];
}
}
