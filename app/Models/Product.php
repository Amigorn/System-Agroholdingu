<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'unit_id'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * Pobiera cenę netto produktu obowiązującą w zadanym dniu.
     */
    public function getPriceForDate($date): float
    {
        if (!$date) {
            return 0.0;
        }

        $priceRecord = $this->prices()
            ->where('valid_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->where('valid_until', '>=', $date)
                      ->orWhereNull('valid_until');
            })
            ->latest('valid_from') // Na wypadek nakładających się przedziałów bierzemy najnowszą
            ->first();

        return $priceRecord ? (float) $priceRecord->price : 0.0;
    }
}