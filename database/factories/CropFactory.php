<?php

namespace Database\Factories;

use App\Models\Field;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

class CropFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Pszenica jara',
                'Jęczmień jary',
                'Kukurydza na kiszonkę',
                'Kukurydza na ziarno',
                'Owies',
                'Słonecznik',
                'Burak cukrowy',
                'Soja',
                'Groch siewny',
                'Łubin żółty',
                'Rzepik jary',
            ]),
            'description' => fake()->optional()->sentence(),
            'field_id' => Field::factory(),
            'season_id' => Season::factory(),
        ];
    }
}