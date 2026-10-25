<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Field;
use App\Models\Machine;
use App\Models\Task;
use App\Models\Crop;
use App\Models\Resource;
use App\Models\ResourceConsumption;
use App\Models\Harvest;
use App\Models\UnitOfMeasure;
use App\Models\Product;
use App\Models\Season;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Stały Admin (Dodany bezpiecznie na samym początku metody)
        User::factory()->create([
    'first_name' => 'Patryk',
    'last_name' => 'Admin',
    'email' => 'admin@local.test',
    'password' => bcrypt('password'),
    'role' => 'admin',
]);

        // 2. Definiowanie stałych jednostek miar
        $kg = UnitOfMeasure::factory()->create(['nazwa' => 'Kilogram', 'skrot' => 'kg']);
        $l = UnitOfMeasure::factory()->create(['nazwa' => 'Litr', 'skrot' => 'l']);
        $t = UnitOfMeasure::factory()->create(['nazwa' => 'Tona', 'skrot' => 't']);

        // 3. Sezony uprawowe
        $sezon = Season::factory()->create(['rok' => 2026, 'nazwa' => 'Sezon Główny 2026']);

        // 4. Generowanie zasobów nadrzędnych
        $workers = User::factory(10)->create(); 
        $fields = Field::factory(10)->create(); 
        $machines = Machine::factory(10)->create();

        $surowce = Resource::factory(6)->create([
            'jednostka_id' => fn() => fake()->randomElement([$kg->id, $l->id])
        ]);

        $produkty = Product::factory(6)->create([
            'jednostka_id' => $t->id
        ]);

        $uprawy = Crop::factory(5)->create([
            'pole_id' => fn() => $fields->random()->id,
            'sezon_uprawowy_id' => $sezon->id
        ]);

        // 5. Generowanie Zadań i Zużycia surowców
        for ($i = 0; $i < 20; $i++) {
            $task = Task::factory()->create([
                'user_id' => $workers->random()->id,
                'machine_id' => fake()->boolean(80) ? $machines->random()->id : null,
                'field_id' => $fields->random()->id,
            ]);

            if (fake()->boolean(70)) {
                ResourceConsumption::factory()->create([
                    'zadanie_id' => $task->id,
                    'surowiec_id' => $surowce->random()->id,
                ]);
            }
        }

        // 6. Generowanie Zbiorów
        for ($i = 0; $i < 10; $i++) {
            $uprawa = $uprawy->random();
            Harvest::factory()->create([
                'jednostka_id' => $t->id,
                'uprawa_id' => $uprawa->id,
                'pole_id' => $uprawa->pole_id,
                'produkt_id' => $produkty->random()->id,
            ]);
        }
    } // <-- Tutaj poprawnie kończy się metoda run()
} // <-- Tutaj kończy się klasa DatabaseSeeder