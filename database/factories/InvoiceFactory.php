<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'number' => 'FV/' . fake()->unique()->numerify('###/2026'),
            'issue_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'type' => fake()->randomElement(['purchase', 'sale']),
            'contractor' => fake()->company(),
            'total' => fake()->randomFloat(2, 1000, 50000), // Całkowita wartość faktury
            'description' => fake()->optional()->sentence(),
        ];
    }
}