<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $products = [
            ['name' => 'Pszenica ozima', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Olej rzepakowy', 'unit' => 'l', 'type' => 'produkt_przetworzony'],
            ['name' => 'Rzepak ozimy', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Mąka pszenna', 'unit' => 't', 'type' => 'produkt_przetworzony'],
            ['name' => 'Kukurydza na ziarno', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Olej słonecznikowy', 'unit' => 'l', 'type' => 'produkt_przetworzony'],
            ['name' => 'Jęczmień jary', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Śruta rzepakowa', 'unit' => 't', 'type' => 'produkt_przetworzony'],
            ['name' => 'Burak cukrowy', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Słonecznik', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Mąka żytnia', 'unit' => 't', 'type' => 'produkt_przetworzony'],
            ['name' => 'Groch siewny', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Soja', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Żyto ozime', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Owies', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Otręby pszenne', 'unit' => 't', 'type' => 'produkt_przetworzony'],
        ];

        $product = fake()->randomElement($products);

        $unit = UnitOfMeasure::where('symbol', $product['unit'])->first();

        // Jeśli nie ma litra, użyj tony lub innej dostępnej jednostki.
        if (!$unit && $product['unit'] === 'l') {
            $unit = UnitOfMeasure::where('symbol', 't')->first();
        }

        return [
            'name' => $product['name'],
            'type' => $product['type'],
            'unit_id' => $unit?->id ?? UnitOfMeasure::factory(),
        ];
    }
}
