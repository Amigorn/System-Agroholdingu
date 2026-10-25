<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MachineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['John Deere', 'New Holland', 'Case IH', 'Claars', 'Massey Ferguson']) . ' ' . fake()->numberBetween(100, 900),
            'type' => fake()->randomElement(['Tractor', 'Combine Harvester', 'Plough', 'Seeder', 'Sprayer']),
            'status' => fake()->randomElement(['available', 'in_use', 'under_repair']),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
