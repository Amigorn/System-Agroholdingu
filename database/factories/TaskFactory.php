<?php

namespace Database\Factories;

use App\Models\Field;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(['planned', 'in_progress', 'completed']);

        $plannedAt = match ($status) {
            'in_progress' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'completed' => fake()->dateTimeBetween('-1 month', '-1 day'),
            default => fake()->dateTimeBetween('-1 week', '+1 month'),
        };

        $plannedEndAt = (clone $plannedAt)->modify('+' . fake()->numberBetween(2, 12) . ' hours');

        $startedAt = match ($status) {
            'in_progress' => fake()->dateTimeBetween($plannedAt, 'now'),
            'completed' => fake()->dateTimeBetween($plannedAt, $plannedEndAt),
            default => null,
        };

        $completedAt = $status === 'completed'
            ? fake()->dateTimeBetween($startedAt ?? $plannedAt, (clone $plannedEndAt)->modify('+1 day'))
            : null;

        return [
            'type' => fake()->randomElement(['Orka', 'Siew', 'Oprysk', 'Nawożenie']),
            'planned_at' => $plannedAt,
            'planned_end_at' => $plannedEndAt,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'status' => $status,
            'description' => fake()->optional()->text(100),
            'user_id' => User::inRandomOrder()->first()?->id,
            'field_id' => Field::inRandomOrder()->first()?->id ?? Field::factory(),
        ];
    }
}