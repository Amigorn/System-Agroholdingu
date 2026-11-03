<?php

namespace Database\Factories;

use App\Models\Field;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['planned', 'in_progress', 'completed']);
        $plannedAt = fake()->dateTimeBetween('-1 month', '+1 month');
        
        $completedAt = $status === 'completed' 
            ? fake()->dateTimeBetween($plannedAt, (clone $plannedAt)->modify('+3 days')) 
            : null;

        return [
            'type' => fake()->randomElement(['Orka', 'Siew', 'Oprysk', 'Omłot', 'Nawożenie']),
            'planned_at' => $plannedAt,
            'completed_at' => $completedAt,
            'status' => $status,
            'description' => fake()->optional()->text(100),
            
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'field_id' => Field::inRandomOrder()->first()?->id ?? Field::factory(),
            
            // Ciągnik
            'tractor_id' => Machine::where('type', 'Ciągnik')->inRandomOrder()->first()?->id 
                ?? Machine::factory()->state(['type' => 'Ciągnik'])->create()->id,
            
            // Maszyna towarzysząca (z dodanym ->create()->id)
            'machine_id' => Machine::where('type', '!=', 'Ciągnik')->inRandomOrder()->first()?->id 
                ?? Machine::factory()->state(['type' => 'Pług'])->create()->id,
        ];
    }
}