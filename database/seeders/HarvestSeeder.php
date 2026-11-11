<?php

namespace Database\Seeders;

use App\Models\Harvest;
use App\Models\Crop;
use App\Models\Product;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class HarvestSeeder extends Seeder
{
    public function run(): void
    {
        $crops = Crop::all();
        $products = Product::all();
        $t = UnitOfMeasure::where('symbol', 't')->first();

        if ($crops->isEmpty() || $products->isEmpty() || !$t) {
            return;
        }

        for ($i = 0; $i < 15; $i++) {
            $crop = $crops->random();
            Harvest::factory()->create([
                'unit_id' => $t->id,
                'crop_id' => $crop->id,
                'field_id' => $crop->field_id,
                'product_id' => $products->random()->id,
            ]);
        }
    }
}