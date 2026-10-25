<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['planned', 'in_progress', 'completed']);
        $plannedAt = fake()->dateTimeBetween('-1 month', '+1 month');
        
        // Klonujemy datę planowaną i dodajemy do niej bezpiecznie 3 dni, 
        // dzięki czemu data końcowa zawsze będzie PO dacie rozpoczęcia.
        $completedAt = $status === 'completed' 
            ? fake()->dateTimeBetween($plannedAt, (clone $plannedAt)->modify('+3 days')) 
            : null;

        return [
            'type' => fake()->randomElement(['Ploughing', 'Sowing', 'Spraying', 'Harvesting', 'Fertilizing']),
            'planned_at' => $plannedAt,
            'completed_at' => $completedAt,
            'status' => $status,
            'description' => fake()->optional()->text(100),
            'user_id' => \App\Models\User::factory(),
            'field_id' => \App\Models\Field::factory(),
            'machine_id' => \App\Models\Machine::factory(),
        ];
    }
}