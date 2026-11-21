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
            ['name' => 'Pszenica jara', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Mąka pszenna', 'unit' => 't', 'type' => 'produkt_przetworzony'],
            ['name' => 'Kukurydza na ziarno', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Olej słonecznikowy', 'unit' => 'l', 'type' => 'produkt_przetworzony'],
            ['name' => 'Jęczmień jary', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Burak cukrowy', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Słonecznik', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Groch siewny', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Soja', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Owies', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Otręby pszenne', 'unit' => 't', 'type' => 'produkt_przetworzony'],
            ['name' => 'Łubin żółty', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Rzepik jary', 'unit' => 't', 'type' => 'plon'],
            ['name' => 'Olej rzepikowy', 'unit' => 'l', 'type' => 'produkt_przetworzony'],
            ['name' => 'Płatki owsiane', 'unit' => 't', 'type' => 'produkt_przetworzony'],
        ];

        $product = fake()->randomElement($products);

        $unit = UnitOfMeasure::where('symbol', $product['unit'])->first();

        if (! $unit && $product['unit'] === 'l') {
            $unit = UnitOfMeasure::where('symbol', 't')->first();
        }

        return [
            'name' => $product['name'],
            'type' => $product['type'],
            'unit_id' => $unit?->id ?? UnitOfMeasure::factory(),
        ];
    }
}