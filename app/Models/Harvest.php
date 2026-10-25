<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Harvest extends Model
{
    /** @use HasFactory<\Database\Factories\HarvestFactory> */
    use HasFactory;

    protected $fillable = ['data_zbioru', 'ilosc', 'jednostka_id', 'uprawa_id', 'pole_id', 'produkt_id'];

public function jednostka() { return $this->belongsTo(UnitOfMeasure::class, 'jednostka_id'); }
public function uprawa() { return $this->belongsTo(Crop::class, 'uprawa_id'); }
public function pole() { return $this->belongsTo(Field::class, 'pole_id'); }
public function produkt() { return $this->belongsTo(Product::class, 'produkt_id'); }
}
