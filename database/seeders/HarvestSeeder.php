<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\Harvest;
use App\Models\Product;
use App\Models\UnitOfMeasure;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HarvestSeeder extends Seeder
{
    public function run(): void
    {
        $crops = Crop::all();
        $t = UnitOfMeasure::where('symbol', 't')->first();
        $l = UnitOfMeasure::whereIn('symbol', ['l', 'litr'])->first() ?? $t;

        if ($crops->isEmpty() || ! $t) {
            return;
        }

        // Mapa powiązań: Nazwa uprawy na polu -> Nazwa produktu (również przetworzonego)
        $transformationMap = [
            'Pszenica jara' => 'Mąka pszenna',
            'Słonecznik' => 'Olej słonecznikowy',
            'Rzepik jary' => 'Olej rzepikowy',
            'Owies' => 'Płatki owsiane',
        ];

        for ($i = 0; $i < 15; $i++) {
            $crop = $crops->random();

            $targetProductName = $transformationMap[$crop->name] ?? null;

            $product = null;
            if ($targetProductName) {
                $product = Product::where('name', $targetProductName)->first();
            }

            if (! $product) {
                $product = Product::where('name', $crop->name)->first()
                    ?? Product::query()
                        ->where('type', 'plon')
                        ->whereRaw('LOWER(name) NOT LIKE ?', ['%ozim%'])
                        ->inRandomOrder()
                        ->first();
            }

            $resolvedProduct = $product ?? Product::first();
            $unitId = $resolvedProduct?->unit_id ?? $t->id;
            $harvestDate = $this->resolveHarvestDate($crop);

            Harvest::factory()->create([
                'unit_id' => $unitId ?: $l->id,
                'crop_id' => $crop->id,
                'field_id' => $crop->field_id,
                'product_id' => $resolvedProduct->id,
                'date' => $harvestDate,
            ]);
        }
    }

    protected function resolveHarvestDate(Crop $crop): string
    {
        $sowingDate = $crop->sowing_date
            ? Carbon::parse($crop->sowing_date)->startOfDay()
            : now()->subMonths(4)->startOfDay();

        $cropName = mb_strtolower((string) $crop->name, 'UTF-8');
        $harvestYear = $sowingDate->year;

        // Okno zbioru dla danego typu uprawy (miesiąc początkowy i końcowy w roku zbioru)
        [$startMonth, $endMonth] = match (true) {
            str_contains($cropName, 'kukurydz'),
            str_contains($cropName, 'burak'),
            str_contains($cropName, 'soj'),
            str_contains($cropName, 'słonecznik') => [9, 10],
            default => [7, 8],
        };

        $windowStart = Carbon::create($harvestYear, $startMonth, 1)->startOfDay();
        $windowEnd = Carbon::create($harvestYear, $endMonth, 1)->endOfMonth()->startOfDay();

        // Zbiór musi odbyć się po dacie siewu (minimum 60 dni wegetacji)
        $minAllowedDate = $sowingDate->copy()->addDays(60);
        $effectiveStart = $windowStart->greaterThan($minAllowedDate) ? $windowStart : $minAllowedDate;

        // Nie wykraczamy poza dzisiaj
        $today = now()->startOfDay();
        $effectiveEnd = $windowEnd->greaterThan($today) ? $today : $windowEnd;

        if ($effectiveEnd->lessThan($effectiveStart)) {
            $fallback = $sowingDate->copy()->addDays(75);

            return ($fallback->lessThanOrEqualTo($today) ? $fallback : $today)->toDateString();
        }

        $daysDiff = $effectiveStart->diffInDays($effectiveEnd);

        return $effectiveStart
            ->copy()
            ->addDays(fake()->numberBetween(0, $daysDiff))
            ->toDateString();
    }
}