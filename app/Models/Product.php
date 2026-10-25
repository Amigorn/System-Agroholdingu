<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = ['nazwa', 'typ', 'jednostka_id'];

public function jednostka() { 
    return $this->belongsTo(UnitOfMeasure::class, 'jednostka_id'); 
}
}