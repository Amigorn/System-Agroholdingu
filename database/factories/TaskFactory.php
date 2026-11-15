<?php

namespace Database\Factories;

use App\Models\Field;
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
            'type' => fake()->randomElement(['Orka', 'Siew', 'Oprysk', 'Nawożenie']),
            'planned_at' => $plannedAt,
            'completed_at' => $completedAt,
            'status' => $status,
            'description' => fake()->optional()->text(100),
            
            // Zostaje tylko pole powiązania z polem uprawnym
            'field_id' => Field::inRandomOrder()->first()?->id ?? Field::factory(),
        ];
    }
}