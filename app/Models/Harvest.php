<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Harvest extends Model
{
    /** @use HasFactory<\Database\Factories\HarvestFactory> */
    use HasFactory;

    protected $fillable = [
        'date',
        'quantity',
        'unit_id',
        'crop_id',
        'field_id',
        'product_id',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_id');
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class, 'crop_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class, 'field_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}