<?php

namespace Database\Factories;

use App\Models\Resource;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResourceConsumptionFactory extends Factory
{
    public function definition(): array
{
    return [
        'ilosc' => fake()->randomFloat(2, 10, 300),
        'zadanie_id' => Task::factory(),
        'surowiec_id' => Resource::factory(),
    ];
}
}