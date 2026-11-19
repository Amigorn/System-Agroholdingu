<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Jedyny administrator w systemie
        User::factory()->create([
            'first_name' => 'Patryk',
            'last_name' => 'Admin',
            'email' => 'admin@local.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Dodatkowi magazynierzy i kierownicy (bez roli admin)
        User::factory(5)->create([
            'role' => 'warehouseman',
        ]);

        User::factory(4)->create([
            'role' => 'manager',
        ]);

        // Pracownicy (w tym operatorzy do zestawów roboczych)
        User::factory(30)->create([
            'role' => 'worker',
        ]);
    }
}