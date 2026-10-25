<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceConsumption extends Model
{
    /** @use HasFactory<\Database\Factories\ResourceConsumptionFactory> */
    use HasFactory;

   protected $fillable = ['ilosc', 'zadanie_id', 'surowiec_id'];

public function zadanie() { 
    return $this->belongsTo(Task::class, 'zadanie_id'); 
}
public function surowiec() { 
    return $this->belongsTo(Resource::class, 'surowiec_id'); 
} 
}

