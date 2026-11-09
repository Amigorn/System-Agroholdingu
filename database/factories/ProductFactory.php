<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UnitOfMeasure;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Pszenica ozima', 
                'Jęczmień jary', 
                'Rzepak ozimy', 
                'Kukurydza ziarno', 
                'Buraki cukrowe'
            ]),
            'type' => 'crop', // Zgodne z naszym systemem: plon / zbiór
            'unit_id' => UnitOfMeasure::inRandomOrder()->first()?->id ?? UnitOfMeasure::factory(),
        ];
    }
}