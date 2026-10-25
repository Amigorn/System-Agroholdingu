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
            'quantity' => fake()->randomFloat(2, 10, 300),
            'task_id' => Task::factory(),
            'resource_id' => Resource::factory(),
        ];
    }
}