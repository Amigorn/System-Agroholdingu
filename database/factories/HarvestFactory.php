<?php

namespace Database\Factories;

use App\Models\Crop;
use App\Models\Field;
use App\Models\Harvest;
use App\Models\Product;
use App\Models\UnitOfMeasure;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class HarvestFactory extends Factory
{
    protected $model = Harvest::class;

    public function definition(): array
    {
        return [
            'date' => function (array $attributes): string {
                $crop = null;

                if (! empty($attributes['crop_id'])) {
                    $crop = $attributes['crop_id'] instanceof Crop
                        ? $attributes['crop_id']
                        : Crop::find($attributes['crop_id']);
                }

                $minDate = $crop?->sowing_date
                    ? Carbon::parse($crop->sowing_date)->addDays(60)->startOfDay()
                    : now()->subMonths(3)->startOfDay();

                $maxDate = now()->startOfDay();

                if ($minDate->greaterThan($maxDate)) {
                    $minDate = $maxDate->copy()->subDays(14);
                }

                $daysDiff = $minDate->diffInDays($maxDate);

                return $minDate->copy()
                    ->addDays($daysDiff > 0 ? fake()->numberBetween(0, $daysDiff) : 0)
                    ->toDateString();
            },
            'quantity' => fake()->randomFloat(2, 5, 100),
            'unit_id' => UnitOfMeasure::factory(),
            'crop_id' => Crop::factory(),
            'field_id' => fn (array $attributes) => Crop::find($attributes['crop_id'])?->field_id ?? Field::factory(),
            'product_id' => Product::factory(),
        ];
    }
}