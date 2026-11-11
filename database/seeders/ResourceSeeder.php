<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $kg = UnitOfMeasure::where('symbol', 'kg')->first();
        $l = UnitOfMeasure::where('symbol', 'l')->first();

        Resource::factory(10)->create([
            'unit_id' => fn() => fake()->randomElement([$kg?->id, $l?->id])
        ]);
    }
}