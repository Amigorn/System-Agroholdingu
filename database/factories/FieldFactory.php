<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FieldFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Pole ' . fake()->unique()->colorName() . ' ' . fake()->randomDigitNotNull(),
            'area' => fake()->randomFloat(2, 2, 45), // Powierzchnia od 2.00 do 45.00 ha
            'soil_type' => fake()->randomElement(['Klasa I', 'Klasa II', 'Klasa IIIa', 'Klasa IIIb', 'Klasa IV']),
            'location' => fake()->latitude(51.0, 53.0) . ', ' . fake()->longitude(16.0, 19.0), // Współrzędne w PL
            'description' => fake()->optional()->sentence(),
        ];
    }
}
