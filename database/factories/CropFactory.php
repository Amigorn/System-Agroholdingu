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
            'name' => fake()->randomElement(['Pszenica Ozima', 'Rzepak Ozimy', 'Kukurydza na kiszonkę']),
            'description' => fake()->optional()->sentence(),
            'field_id' => Field::factory(),
            'season_id' => Season::factory(),
        ];
    }
}