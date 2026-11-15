<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Field;
use App\Models\Machine;
use App\Models\User;
use App\Models\TaskMachine;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $fields = Field::all();
        $workers = User::all();
        $tractors = Machine::where('type', 'Ciągnik')->get();
        $machines = Machine::where('type', '!=', 'Ciągnik')->get();

        if ($fields->isEmpty() || $tractors->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 10; $i++) {
            $task = Task::factory()->create([
                'field_id' => $fields->random()->id,
            ]);

            $setsCount = rand(1, 2);
            for ($j = 0; $j < $setsCount; $j++) {
                TaskMachine::create([
                    'task_id' => $task->id,
                    'tractor_id' => $tractors->random()->id,
                    'machine_id' => $machines->isNotEmpty() ? $machines->random()->id : null,
                    'user_id' => $workers->isNotEmpty() ? $workers->random()->id : null,
                    'notes' => fake()->optional()->sentence(4), // Opcjonalne uwagi testowe
                ]);
            }
        }
    }
}