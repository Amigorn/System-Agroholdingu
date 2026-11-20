<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\ResourceConsumption;
use App\Models\Task;
use Illuminate\Database\Seeder;

class ResourceConsumptionSeeder extends Seeder
{
    public function run(): void
    {
        // Usuwamy ewentualne zużycia przypisane do zadań innych niż ukończone
        ResourceConsumption::query()
            ->whereHas('task', fn ($q) => $q->where('status', '!=', 'completed'))
            ->delete();

        $completedTasks = Task::where('status', 'completed')->get();
        $resources = Resource::all();

        if ($completedTasks->isEmpty() || $resources->isEmpty()) {
            return;
        }

        foreach ($completedTasks as $task) {
            if (fake()->boolean(70)) {
                ResourceConsumption::factory()->create([
                    'task_id' => $task->id,
                    'resource_id' => $resources->random()->id,
                ]);
            }
        }
    }
}