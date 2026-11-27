<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'status',
        'description',
    ];

    public function tasksAsTractor(): HasMany
    {
        return $this->hasMany(TaskMachine::class, 'tractor_id');
    }

    public function tasksAsMachine(): HasMany
    {
        return $this->hasMany(TaskMachine::class, 'machine_id');
    }
}