<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    use HasFactory;

    protected $table = 'product_prices';

    protected $fillable = [
        'product_id',
        'price',
        'unit_id',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'unit_id');
    }
}