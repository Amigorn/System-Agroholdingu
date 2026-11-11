<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Models\Field;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $workers = User::all();
        $fields = Field::all();

        if ($workers->isEmpty() || $fields->isEmpty()) {
            return;
        }

        for ($i = 0; $i < 20; $i++) {
            Task::factory()->create([
                'user_id' => $workers->random()->id,
                'field_id' => $fields->random()->id,
            ]);
        }
    }
}