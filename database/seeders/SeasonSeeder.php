<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        Season::factory()->create(['year' => 2024, 'name' => 'Sezon uprawowy 2024']);
        Season::factory()->create(['year' => 2025, 'name' => 'Sezon uprawowy 2025']);
        Season::factory()->create(['year' => 2026, 'name' => 'Sezon uprawowy 2026']);
    }
}