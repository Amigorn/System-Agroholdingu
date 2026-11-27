<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'unit_id',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getPriceForDate(DateTimeInterface|string|null $date): float
    {
        if (! $date) {
            return 0.0;
        }

        $targetDate = Carbon::parse($date)->toDateString();

        $priceRecord = $this->prices()
            ->whereDate('valid_from', '<=', $targetDate)
            ->where(function ($query) use ($targetDate) {
                $query->whereDate('valid_until', '>=', $targetDate)
                    ->orWhereNull('valid_until');
            })
            ->latest('valid_from')
            ->first();

        return $priceRecord ? (float) $priceRecord->price : 0.0;
    }
}