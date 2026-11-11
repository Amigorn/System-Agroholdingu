<?php

namespace Database\Seeders;

use App\Models\ResourceConsumption;
use App\Models\Task;
use App\Models\Resource;
use Illuminate\Database\Seeder;

class ResourceConsumptionSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = Task::all();
        $resources = Resource::all();

        if ($tasks->isEmpty() || $resources->isEmpty()) {
            return;
        }

        foreach ($tasks as $task) {
            if (fake()->boolean(70)) {
                ResourceConsumption::factory()->create([
                    'task_id' => $task->id,
                    'resource_id' => $resources->random()->id,
                ]);
            }
        }
    }
}