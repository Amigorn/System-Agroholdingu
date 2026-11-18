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

        // 1. Na start resetujemy status wszystkich maszyn na dostępne
        Machine::query()->update(['status' => 'available']);

        for ($i = 0; $i < 10; $i++) {
            // Fabryka losuje status zadania: 'planned', 'in_progress', lub 'completed'
            $task = Task::factory()->create([
                'field_id' => $fields->random()->id,
            ]);

            $setsCount = rand(1, 2);
            for ($j = 0; $j < $setsCount; $j++) {
                $tractor = $tractors->random();
                $machine = $machines->isNotEmpty() ? $machines->random() : null;
                $worker = $workers->isNotEmpty() ? $workers->random() : null;

                TaskMachine::create([
                    'task_id' => $task->id,
                    'tractor_id' => $tractor->id,
                    'machine_id' => $machine?->id,
                    'user_id' => $worker?->id,
                    'notes' => fake()->optional()->sentence(4),
                ]);

                // 2. Synchronizacja: Jeśli zadanie jest w toku, pojazdy i maszyny stają się "w użyciu"
                if ($task->status === 'in_progress') {
                    Machine::where('id', $tractor->id)->update(['status' => 'in_use']);
                    
                    if ($machine !== null) {
                        Machine::where('id', $machine->id)->update(['status' => 'in_use']);
                    }
                }
            }
        }

        // 3. NA SAMYM KOŃCU: Losowo wybieramy kilka maszyn z tych, które zostały "available" 
        // i zmieniamy ich status na "under_repair" (dzięki temu zadania ich nie nadpiszą)
        Machine::where('status', 'available')
            ->inRandomOrder()
            ->take(rand(2, 4)) // weźmie losowo 2 do 4 sztuk sprzętu
            ->update(['status' => 'under_repair']);
    }
}