<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\Field;
use App\Models\Product;
use App\Models\Season;
use Illuminate\Database\Seeder;

class CropSeeder extends Seeder
{
    public function run(): void
    {
        $fields = Field::all();
        $seasons = Season::all();

        $cropNames = Product::query()
            ->where('type', 'plon')
            ->whereRaw('LOWER(name) NOT LIKE ?', ['%ozim%'])
            ->pluck('name')
            ->toArray();

        if (empty($cropNames) || $fields->isEmpty() || $seasons->isEmpty()) {
            return;
        }

        foreach ($fields as $field) {
            foreach ($seasons as $season) {
                Crop::factory()->create([
                    'field_id' => $field->id,
                    'season_id' => $season->id,
                    'name' => fake()->randomElement($cropNames),
                ]);
            }
        }
    }
}