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
        // 1. Admin
        User::factory()->create([
            'first_name' => 'Patryk',
            'last_name' => 'Admin',
            'email' => 'admin@local.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 2. Units of Measure
        $kg = UnitOfMeasure::factory()->create(['name' => 'Kilogram', 'symbol' => 'kg']);
        $l = UnitOfMeasure::factory()->create(['name' => 'Liter', 'symbol' => 'l']);
        $t = UnitOfMeasure::factory()->create(['name' => 'Tonne', 'symbol' => 't']);

        // 3. Seasons
        $season = Season::factory()->create(['year' => 2026, 'name' => 'Sezon uprawowy 2026']);

        // 4. Parent resources
        $workers = User::factory(10)->create(); 
        $fields = Field::factory(10)->create(); 
        $machines = Machine::factory(10)->create();

        $resources = Resource::factory(10)->create([
            'unit_id' => fn() => fake()->randomElement([$kg->id, $l->id])
        ]);

        $products = Product::factory(10)->create([
            'unit_id' => $t->id
        ]);

        $crops = Crop::factory(10)->create([
            'field_id' => fn() => $fields->random()->id,
            'season_id' => $season->id
        ]);

        // 5. Tasks and Resource Consumptions
        for ($i = 0; $i < 20; $i++) {
            $task = Task::factory()->create([
                'user_id' => $workers->random()->id,
                'machine_id' => fake()->boolean(80) ? $machines->random()->id : null,
                'field_id' => $fields->random()->id,
            ]);

            if (fake()->boolean(70)) {
                ResourceConsumption::factory()->create([
                    'task_id' => $task->id,
                    'resource_id' => $resources->random()->id,
                ]);
            }
        }

        // 6. Harvests
        for ($i = 0; $i < 10; $i++) {
            $crop = $crops->random();
            Harvest::factory()->create([
                'unit_id' => $t->id,
                'crop_id' => $crop->id,
                'field_id' => $crop->field_id,
                'product_id' => $products->random()->id,
            ]);
        }
    }
}