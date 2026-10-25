<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    /** @use HasFactory<\Database\Factories\CropFactory> */
    use HasFactory;

   protected $fillable = ['nazwa', 'opis', 'pole_id', 'sezon_uprawowy_id'];

public function pole() { 
    return $this->belongsTo(Field::class, 'pole_id'); 
}
public function sezonUprawowy() { 
    return $this->belongsTo(Season::class, 'sezon_uprawowy_id'); 
}
}
