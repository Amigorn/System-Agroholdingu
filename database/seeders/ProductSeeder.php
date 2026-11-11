<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Pobieramy domyślną jednostkę (np. tony)
        $t = UnitOfMeasure::where('symbol', 't')->first() ?? UnitOfMeasure::first();

        // Lista unikalnych, roślinnych produktów rolniczych
        $uniqueProducts = [
            'Pszenica ozima',
            'Rzepak ozimy',
            'Kukurydza na ziarno',
            'Jęczmień jary',
            'Burak cukrowy',
            'Groch siewny',
            'Soja',
            'Słonecznik',
            'Żyto ozime',
            'Owies',
        ];

        foreach ($uniqueProducts as $productName) {
            Product::firstOrCreate(
                ['name' => $productName],
                [
                    'unit_id' => $t?->id,
                    'type' => 'plon', // Wypełniamy wymagane pole 'type'
                ]
            );
        }
    }
}