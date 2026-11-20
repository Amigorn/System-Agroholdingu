<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tonne = UnitOfMeasure::where('symbol', 't')->first()
            ?? UnitOfMeasure::first();

        $litre = UnitOfMeasure::whereIn('symbol', ['l', 'litr'])->first()
            ?? $tonne;

        if (!$tonne) {
            throw new RuntimeException(
                'Brak jednostek miary. Najpierw uruchom UnitOfMeasureSeeder.'
            );
        }

        $productsToSeed = [
            ['name' => 'Pszenica ozima', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Olej rzepakowy', 'unit' => $litre, 'type' => 'produkt_przetworzony'],
            ['name' => 'Rzepak ozimy', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Mąka pszenna', 'unit' => $tonne, 'type' => 'produkt_przetworzony'],
            ['name' => 'Kukurydza na ziarno', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Olej słonecznikowy', 'unit' => $litre, 'type' => 'produkt_przetworzony'],
            ['name' => 'Jęczmień jary', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Śruta rzepakowa', 'unit' => $tonne, 'type' => 'produkt_przetworzony'],
            ['name' => 'Burak cukrowy', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Słonecznik', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Mąka żytnia', 'unit' => $tonne, 'type' => 'produkt_przetworzony'],
            ['name' => 'Groch siewny', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Soja', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Żyto ozime', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Owies', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Otręby pszenne', 'unit' => $tonne, 'type' => 'produkt_przetworzony'],
        ];

        foreach ($productsToSeed as $item) {
            Product::updateOrCreate(
                ['name' => $item['name']],
                [
                    'unit_id' => $item['unit']->id,
                    'type' => $item['type'],
                ]
            );
        }
    }
}
