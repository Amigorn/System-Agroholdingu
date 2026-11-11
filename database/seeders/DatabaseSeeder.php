<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Administrator gospodarstwa
        User::factory()->create([
            'first_name' => 'Patryk',
            'last_name' => 'Admin',
            'email' => 'admin@local.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Pracownicy
        User::factory(10)->create();

        // 2-14. Pozostałe seedery systemowe
        $this->call([
            UnitOfMeasureSeeder::class,      // Jednostki miary (kg, l, t)
            WarehouseSeeder::class,          // Magazyny
            SeasonSeeder::class,             // Sezony uprawowe (2024, 2025, 2026)
            FieldSeeder::class,              // Pola gospodarstwa
            MachineSeeder::class,            // Maszyny i ciągniki
            ResourceSeeder::class,           // Zasoby (środki do produkcji)
            ProductSeeder::class,            // Produkty roślinne
            CropSeeder::class,               // Płodozmian (powiązanie pól z sezonami)
            TaskSeeder::class,               // Zadania / zabiegi agrotechniczne
            ResourceConsumptionSeeder::class,// Zużycie zasobów w zadaniach
            HarvestSeeder::class,            // Zbiory z upraw
            InvoiceSeeder::class,            // Nagłówki faktur
            InvoiceItemSeeder::class,        // Pozycje faktur
        ]);
    }
}