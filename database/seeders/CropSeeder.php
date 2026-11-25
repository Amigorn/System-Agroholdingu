<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\Field;
use App\Models\Product;
use App\Models\Season;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CropSeeder extends Seeder
{
    public function run(): void
    {
        $fields = Field::all();
        $seasons = Season::all();

        $cropNames = Product::query()
            ->where('type', 'plon')
            ->whereRaw('LOWER(name) NOT LIKE ?', ['%ozim%'])
            ->pluck('name')
            ->toArray();

        if (empty($cropNames) || $fields->isEmpty() || $seasons->isEmpty()) {
            return;
        }

        foreach ($fields as $field) {
            foreach ($seasons as $season) {
                $name = fake()->randomElement($cropNames);
                $sowingDate = $this->resolveSowingDate($season, $name);

                $crop = Crop::firstOrCreate(
                    [
                        'field_id' => $field->id,
                        'season_id' => $season->id,
                    ],
                    [
                        'name' => $name,
                        'description' => "Uprawa: {$name} na polu {$field->name}",
                        'sowing_date' => $sowingDate,
                    ]
                );

                // Jeśli rekord istniał bez daty siewu, uzupełniamy ją spójnie z sezonem
                if (! $crop->sowing_date) {
                    $crop->update(['sowing_date' => $sowingDate]);
                }
            }
        }
    }

    protected function resolveSowingDate(Season $season, string $cropName): string
    {
        $seasonStart = $season->start_date ?? $season->starts_at ?? null;
        $year = $seasonStart
            ? Carbon::parse($seasonStart)->year
            : (preg_match('/\b(20\d{2})\b/', (string) ($season->name ?? ''), $matches)
                ? (int) $matches[1]
                : now()->year);

        $lowerName = mb_strtolower($cropName, 'UTF-8');

        if (str_contains($lowerName, 'ozim')) {
            return Carbon::create($year - 1, 9, fake()->numberBetween(10, 28))->toDateString();
        }

        if (str_contains($lowerName, 'kukurydz') || str_contains($lowerName, 'soj') || str_contains($lowerName, 'słonecznik')) {
            return Carbon::create($year, 4, fake()->numberBetween(15, 30))->toDateString();
        }

        return Carbon::create($year, 3, fake()->numberBetween(20, 31))
            ->addDays(fake()->numberBetween(0, 15))
            ->toDateString();
    }
}