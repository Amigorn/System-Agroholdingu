<?php

namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Seeder;

class MachineSeeder extends Seeder
{
    public function run(): void
    {
        $factory = Machine::factory();

        $tractors = collect(range(1, 15))
            ->map(fn () => $factory->tractor()->raw());

        $others = collect(range(1, 35))
            ->map(fn () => $factory->raw());

        $tractors->concat($others)
            ->shuffle()
            ->each(fn (array $attributes) => Machine::query()->create($attributes));
    }
}