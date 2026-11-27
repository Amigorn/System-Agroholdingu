<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'planned_at',
        'planned_end_at',
        'started_at',
        'completed_at',
        'status',
        'description',
        'field_id',
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }

    public function taskMachines(): HasMany
    {
        return $this->hasMany(TaskMachine::class);
    }

    public function resourceConsumptions(): HasMany
    {
        return $this->hasMany(ResourceConsumption::class);
    }
}