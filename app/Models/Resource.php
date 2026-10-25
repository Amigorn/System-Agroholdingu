<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    /** @use HasFactory<\Database\Factories\ResourceFactory> */
    use HasFactory;

    protected $fillable = ['nazwa', 'jednostka_id'];

public function jednostka() { 
    return $this->belongsTo(UnitOfMeasure::class, 'jednostka_id'); 
}
}
