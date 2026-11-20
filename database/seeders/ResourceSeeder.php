<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        // Pobieramy jednostki miary, żeby móc je precyzyjnie przypisywać
        $kg = UnitOfMeasure::where('symbol', 'kg')->first();
        $l = UnitOfMeasure::where('symbol', 'l')->first();
        $szt = UnitOfMeasure::where('symbol', 'szt')->first();

        // Lista unikalnych zasobów w gospodarstwie wraz z ich typem i domyślną jednostką
        $resources = [
            ['name' => 'Olej napędowy ON', 'type' => 'paliwo', 'unit_id' => $l?->id],
            ['name' => 'Saletra Amonowa', 'type' => 'nawóz', 'unit_id' => $kg?->id],
            ['name' => 'YaraMila', 'type' => 'nawóz', 'unit_id' => $kg?->id],
            ['name' => 'Roundup 360', 'type' => 'środki ochrony roślin', 'unit_id' => $l?->id],
            ['name' => 'Chwastox', 'type' => 'środki ochrony roślin', 'unit_id' => $l?->id],
            ['name' => 'Pszenica Arkadia C1', 'type' => 'materiał siewny', 'unit_id' => $kg?->id],
            ['name' => 'Jęczmień KWS Kosmos C1', 'type' => 'materiał siewny', 'unit_id' => $kg?->id],
            ['name' => 'Rzepak LG Ambassador', 'type' => 'materiał siewny', 'unit_id' => $kg?->id],
            ['name' => 'Kukurydza LG 31.245', 'type' => 'materiał siewny', 'unit_id' => $kg?->id],
            ['name' => 'Turbulant 3000', 'type' => 'środki ochrony roślin', 'unit_id' => $l?->id],
            ['name' => 'AdBlue', 'type' => 'płyny eksploatacyjne', 'unit_id' => $l?->id],
        ];

        foreach ($resources as $resourceData) {
            Resource::updateOrCreate(
                ['name' => $resourceData['name']],
                [
                    'type' => $resourceData['type'],
                    'unit_id' => $resourceData['unit_id'] ?? $szt?->id,
                ]
            );
        }
    }
}