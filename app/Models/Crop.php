<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    /** @use HasFactory<\Database\Factories\CropFactory> */
    use HasFactory;

    protected $fillable = ['name', 'description', 'field_id', 'season_id'];

    public function field() { 
        return $this->belongsTo(Field::class, 'field_id'); 
    }

    public function season() { 
        return $this->belongsTo(Season::class, 'season_id'); 
    }
}