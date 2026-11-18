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
        $t = UnitOfMeasure::where('symbol', 't')->first();

        if ($crops->isEmpty() || !$t) {
            return;
        }

        // Mapa powiązań: Nazwa uprawy na polu -> Nazwa produktu (może być przetworzony!)
        $transformationMap = [
            'Rzepak ozimy' => 'Śruta rzepakowa', // Możesz zmienić na 'Olej rzepakowy'
            'Pszenica ozima' => 'Mąka pszenna',
            'Słonecznik' => 'Olej słonecznikowy',
            'Żyto ozime' => 'Mąka żytnia',
        ];

        for ($i = 0; $i < 15; $i++) {
            $crop = $crops->random();
            
            // 1. Sprawdzamy, czy dla tej uprawy mamy zdefiniowany produkt przetworzony w mapie
            $targetProductName = $transformationMap[$crop->name] ?? null;

            $product = null;
            if ($targetProductName) {
                $product = Product::where('name', $targetProductName)->first();
            }

            // 2. Jeśli nie ma w mapie, szukamy produktu o tej samej nazwie co uprawa, a w ostateczności losowego plonu
            if (!$product) {
                $product = Product::where('name', $crop->name)->first() 
                    ?? Product::where('type', 'plon')->inRandomOrder()->first();
            }

            Harvest::factory()->create([
                'unit_id' => $t->id,
                'crop_id' => $crop->id,
                'field_id' => $crop->field_id,
                'product_id' => $product?->id ?? Product::first()->id,
            ]);
        }
    }
}