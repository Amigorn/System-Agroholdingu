<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\Field;
use App\Models\Season;
use Illuminate\Database\Seeder;

class CropSeeder extends Seeder
{
    public function run(): void
    {
        $fields = Field::all();
        $seasons = Season::all();

        foreach ($fields as $field) {
            foreach ($seasons as $season) {
                Crop::factory()->create([
                    'field_id' => $field->id,
                    'season_id' => $season->id,
                    'name' => fake()->randomElement(['Pszenica Ozima', 'Rzepak Ozimy', 'Kukurydza na ziarno', 'Jęczmień jary'])
                ]);
            }
        }
    }
}