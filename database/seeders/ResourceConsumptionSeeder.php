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

        // Szukamy zasobu paliwa ON (zgodne z filtrem w TasksTable)
        $fuelResource = Resource::query()
            ->where(function ($q) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%paliwo%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%olej napędowy%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%diesel%'])
                    ->orWhere('name', 'LIKE', '% ON')
                    ->orWhere('name', '=', 'ON');
            })
            ->first();

        $otherResources = $fuelResource
            ? $resources->where('id', '!=', $fuelResource->id)
            : $resources;

        foreach ($completedTasks as $task) {
            if ($fuelResource) {
                $alreadyHasFuel = ResourceConsumption::query()
                    ->where('task_id', $task->id)
                    ->where('resource_id', $fuelResource->id)
                    ->exists();

                if (! $alreadyHasFuel) {
                    ResourceConsumption::create([
                        'task_id' => $task->id,
                        'resource_id' => $fuelResource->id,
                        'quantity' => fake()->randomFloat(2, 8, 85),
                    ]);
                }
            }

            // Opcjonalnie dodatkowy surowiec (np. nawóz / środek ochrony / nasiona)
            if ($otherResources->isNotEmpty() && fake()->boolean(60)) {
                ResourceConsumption::create([
                    'task_id' => $task->id,
                    'resource_id' => $otherResources->random()->id,
                    'quantity' => fake()->randomFloat(2, 1, 30),
                ]);
            }
        }
    }
}