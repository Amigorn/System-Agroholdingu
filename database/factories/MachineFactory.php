<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MachineFactory extends Factory
{
    protected static function nameForType(string $type): string
    {
        return match ($type) {
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
    }

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

        return [
            'name' => static::nameForType($type),
            'type' => $type,
            'status' => fake()->randomElement(['available', 'under_repair']),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function tractor(): static
    {
        return $this->state(fn () => [
            'type' => 'Ciągnik',
            'name' => static::nameForType('Ciągnik'),
        ]);
    }
}