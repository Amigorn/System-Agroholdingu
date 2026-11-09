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
use App\Models\Invoice;       // <-- Import modelu faktury
use App\Models\InvoiceItem; // <-- Import modelu pozycji faktury
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin
        User::factory()->create([
            'first_name' => 'Patryk',
            'last_name' => 'Admin',
            'email' => 'admin@local.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 2. Units of Measure
        $kg = UnitOfMeasure::factory()->create(['name' => 'Kilogramy', 'symbol' => 'kg']);
        $l = UnitOfMeasure::factory()->create(['name' => 'Litry', 'symbol' => 'l']);
        $t = UnitOfMeasure::factory()->create(['name' => 'Tony', 'symbol' => 't']);

        // 2.5. Warehouses (Magazyny w gospodarstwie)
        $warehouses = Warehouse::factory(5)->create();

        // 3. Seasons (Wieloletnia historia dla płodozmianu)
        $seasons = [
            Season::factory()->create(['year' => 2024, 'name' => 'Sezon uprawowy 2024']),
            Season::factory()->create(['year' => 2025, 'name' => 'Sezon uprawowy 2025']),
            Season::factory()->create(['year' => 2026, 'name' => 'Sezon uprawowy 2026']),
        ];

        // 4. Parent resources
        $workers = User::factory(10)->create(); 
        $fields = Field::factory(10)->create(); // np. 5 pól, żeby łatwiej kontrolować widok
        $machines = Machine::factory(10)->create();

        $resources = Resource::factory(10)->create([
            'unit_id' => fn() => fake()->randomElement([$kg->id, $l->id])
        ]);

        $products = Product::factory(10)->create([
            'unit_id' => $t->id
        ]);

        // KLUCZOWA ZMIANA: Płodozmian – dla każdego pola tworzymy uprawę w każdym sezonie po kolei
        $crops = collect();
        foreach ($fields as $field) {
            foreach ($seasons as $season) {
                $crops->push(Crop::factory()->create([
                    'field_id' => $field->id,
                    'season_id' => $season->id,
                    'name' => fake()->randomElement(['Pszenica Ozima', 'Rzepak Ozimy', 'Kukurydza na ziarno', 'Jęczmień jary'])
                ]));
            }
        }

        // 5. Tasks and Resource Consumptions
        for ($i = 0; $i < 20; $i++) {
            $task = Task::factory()->create([
                'user_id' => $workers->random()->id,
                'field_id' => $fields->random()->id,
            ]);

            if (fake()->boolean(70)) {
                ResourceConsumption::factory()->create([
                    'task_id' => $task->id,
                    'resource_id' => $resources->random()->id,
                ]);
            }
        }

        // 6. Harvests (Zbiory powiązane z uprawami i polami)
        for ($i = 0; $i < 15; $i++) {
            $crop = $crops->random();
            Harvest::factory()->create([
                'unit_id' => $t->id,
                'crop_id' => $crop->id,
                'field_id' => $crop->field_id,
                'product_id' => $products->random()->id,
            ]);
        }

        // 7. Invoices & Invoice Items (Faktury i ich pozycje)
        Invoice::factory(15)
            ->has(
                InvoiceItem::factory()
                    ->count(fake()->numberBetween(1, 5))
                    ->state(fn () => [
                        'produkt_id' => $products->random()->id,
                    ]),
                'items'
            )
            ->create();
    }
}