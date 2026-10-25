<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Field;
use App\Models\Season;

class CropFactory extends Factory
{
    public function definition(): array
{
    return [
        'nazwa' => fake()->randomElement(['Pszenica Ozima', 'Rzepak Ozimy', 'Kukurydza na kiszonkę']),
        'opis' => fake()->optional()->sentence(),
        'pole_id' => Field::factory(),
        'sezon_uprawowy_id' => Season::factory(),
    ];
}
}