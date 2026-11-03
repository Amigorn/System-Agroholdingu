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
            'numer' => 'FV/' . fake()->unique()->numerify('###/2026'),
            'data_wystawienia' => fake()->dateTimeBetween('-3 months', 'now'),
            'typ' => fake()->randomElement(['zakup', 'sprzedaz']),
            'kontrahent' => fake()->company(),
            'wartosc' => fake()->randomFloat(2, 1000, 50000), // Całkowita wartość faktury
            'opis' => fake()->optional()->sentence(),
        ];
    }
}