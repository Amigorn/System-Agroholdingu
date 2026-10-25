<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    use HasFactory; // <-- To musi być dokładnie tutaj, wewnątrz klamer klasy!

    protected $fillable = ['name', 'area', 'soil_type', 'location', 'description'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
