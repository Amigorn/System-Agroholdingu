<?php

namespace Database\Factories;

use App\Models\Crop;
use App\Models\Field;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

class CropFactory extends Factory
{
    protected $model = Crop::class;

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
            'sowing_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'field_id' => Field::factory(),
            'season_id' => Season::factory(),
        ];
    }
}