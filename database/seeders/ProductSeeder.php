<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $tonne = UnitOfMeasure::where('symbol', 't')->first() ?? UnitOfMeasure::first();
        $litre = UnitOfMeasure::where('symbol', 'l')->orWhere('symbol', 'litr')->first() ?? $tonne;
        $kg = UnitOfMeasure::where('symbol', 'kg')->first() ?? $tonne;

        // Przemieszana lista zawierająca plony oraz produkty przetworzone od samego początku
        $productsToSeed = [
            ['name' => 'Pszenica ozima', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Olej rzepakowy', 'unit' => $litre, 'type' => 'produkt_przetworzony'], // Przetworzony w top 10
            ['name' => 'Rzepak ozimy', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Mąka pszenna', 'unit' => $tonne, 'type' => 'produkt_przetworzony'],      // Przetworzony w top 10
            ['name' => 'Kukurydza na ziarno', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Olej słonecznikowy', 'unit' => $litre, 'type' => 'produkt_przetworzony'], // Przetworzony w top 10
            ['name' => 'Jęczmień jary', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Śruta rzepakowa', 'unit' => $tonne, 'type' => 'produkt_przetworzony'],  // Przetworzony w top 10
            ['name' => 'Burak cukrowy', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Mąka żytnia', 'unit' => $tonne, 'type' => 'produkt_przetworzony'],      // Przetworzony w top 10
            ['name' => 'Groch siewny', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Soja', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Słonecznik', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Żyto ozime', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Owies', 'unit' => $tonne, 'type' => 'plon'],
            ['name' => 'Otręby pszenne', 'unit' => $tonne, 'type' => 'produkt_przetworzony'],
        ];

        foreach ($productsToSeed as $item) {
            Product::firstOrCreate(
                ['name' => $item['name']],
                [
                    'unit_id' => $item['unit']?->id,
                    'type' => $item['type'],
                ]
            );
        }
    }
}