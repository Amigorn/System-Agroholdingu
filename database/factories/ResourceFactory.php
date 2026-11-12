<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UnitOfMeasure;

class ResourceFactory extends Factory
{
    public function definition(): array
    {
        $resourceMap = [
            'Saletra Amonowa' => 'nawóz',
            'YaraMila' => 'nawóz',
            'Roundup 360' => 'środki ochrony roślin',
            'Chwastox' => 'środki ochrony roślin',
            'Pszenica Arkadia C1' => 'materiał siewny',
            'Jęczmień KWS Kosmos C1' => 'materiał siewny',
            'Rzepak LG Ambassador' => 'materiał siewny',
            'Kukurydza LG 31.245' => 'materiał siewny',
            'Olej Napędowy' => 'paliwo',
            'AdBlue' => 'płyny eksploacyjne',
        ];

        $name = fake()->randomElement(array_keys($resourceMap));

        return [
            'name' => $name,
            'type' => $resourceMap[$name],
            'unit_id' => UnitOfMeasure::inRandomOrder()->first()?->id ?? UnitOfMeasure::factory(),
        ];
    }
}