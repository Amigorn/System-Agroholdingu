<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\Harvest;
use App\Models\Product;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class HarvestSeeder extends Seeder
{
    public function run(): void
    {
        $crops = Crop::all();
        $t = UnitOfMeasure::where('symbol', 't')->first();
        $l = UnitOfMeasure::whereIn('symbol', ['l', 'litr'])->first() ?? $t;

        if ($crops->isEmpty() || ! $t) {
            return;
        }

        // Mapa powiązań: Nazwa uprawy na polu -> Nazwa produktu (również przetworzonego)
        $transformationMap = [
            'Pszenica jara' => 'Mąka pszenna',
            'Słonecznik' => 'Olej słonecznikowy',
            'Rzepik jary' => 'Olej rzepikowy',
            'Owies' => 'Płatki owsiane',
        ];

        for ($i = 0; $i < 15; $i++) {
            $crop = $crops->random();

            $targetProductName = $transformationMap[$crop->name] ?? null;

            $product = null;
            if ($targetProductName) {
                $product = Product::where('name', $targetProductName)->first();
            }

            if (! $product) {
                $product = Product::where('name', $crop->name)->first()
                    ?? Product::query()
                        ->where('type', 'plon')
                        ->whereRaw('LOWER(name) NOT LIKE ?', ['%ozim%'])
                        ->inRandomOrder()
                        ->first();
            }

            $resolvedProduct = $product ?? Product::first();
            $unitId = $resolvedProduct?->unit_id ?? $t->id;

            Harvest::factory()->create([
                'unit_id' => $unitId ?: $l->id,
                'crop_id' => $crop->id,
                'field_id' => $crop->field_id,
                'product_id' => $resolvedProduct->id,
            ]);
        }
    }
}