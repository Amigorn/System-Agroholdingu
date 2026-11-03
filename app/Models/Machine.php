<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use HasFactory; // <-- Musi być tutaj

    protected $fillable = ['name', 'type', 'status', 'description'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function tractor()
{
    return $this->belongsTo(Machine::class, 'tractor_id');
}

public function machine()
{
    return $this->belongsTo(Machine::class, 'machine_id');
}
}