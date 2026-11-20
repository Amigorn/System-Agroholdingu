<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MachineFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement([
            'Ciągnik',
            'Kombajn',
            'Pług',
            'Siewnik',
            'Opryskiwacz',
            'Przyczepa duża',
            'Przyczepa mała',
            'Ładowacz czołowy',
            'Prasownica',
        ]);

        $name = match ($type) {
            'Ciągnik', 'Kombajn' => fake()->randomElement([
                'John Deere',
                'New Holland',
                'Case IH',
                'Claas',
                'Massey Ferguson',
            ]) . ' ' . fake()->numberBetween(100, 900),

            default => fake()->randomElement([
                'Unia',
                'Kverneland',
                'Lemken',
                'Horsch',
                'Pronar',
                'Joskin',
                'Amazone',
            ]) . ' ' . fake()->numberBetween(100, 900),
        };

        return [
            'name' => $name,
            'type' => $type,
            'status' => fake()->randomElement(['available', 'under_repair']),
            'description' => fake()->optional()->sentence(),
        ];
    }
}