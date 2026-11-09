<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => 'Magazyn ' . fake()->unique()->randomElement(['Główny', 'Nawozów', 'Paliw', 'Nasion', 'Zbożowy', 'Maszynowy']),
            'location' => 'Gospodarstwo - sektor ' . fake()->randomLetter(),
        ];
    }
}