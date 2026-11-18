<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\Field;
use App\Models\Season;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CropSeeder extends Seeder
{
    public function run(): void
    {
        $fields = Field::all();
        $seasons = Season::all();

        // Pobieramy nazwy tylko tych produktów, które są typu 'plon'
        $cropNames = Product::where('type', 'plon')->pluck('name')->toArray();

        if (empty($cropNames) || $fields->isEmpty() || $seasons->isEmpty()) {
            return;
        }

        foreach ($fields as $field) {
            foreach ($seasons as $season) {
                Crop::factory()->create([
                    'field_id' => $field->id,
                    'season_id' => $season->id,
                    // Losujemy spośród rzeczywistych plonów z bazy
                    'name' => fake()->randomElement($cropNames)
                ]);
            }
        }
    }
}