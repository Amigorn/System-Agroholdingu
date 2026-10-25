<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    /** @use HasFactory<\Database\Factories\UnitOfMeasureFactory> */
    use HasFactory;

    protected $fillable = ['nazwa', 'skrot'];

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class);
    }
}