<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class UnitOfMeasureSeeder extends Seeder
{
    public function run(): void
    {
        UnitOfMeasure::factory()->create(['name' => 'Kilogramy', 'symbol' => 'kg']);
        UnitOfMeasure::factory()->create(['name' => 'Litry', 'symbol' => 'l']);
        UnitOfMeasure::factory()->create(['name' => 'Tony', 'symbol' => 't']);
    }
}