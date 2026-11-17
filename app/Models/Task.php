<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 
        'planned_at', 
        'completed_at', 
        'status', 
        'description', 
        'user_id', 
        'field_id'
        // Brak tractor_id i machine_id – wszystko idzie przez tabelę pośrednią
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
    return $this->hasMany(ResourceConsumption::class, 'task_id');
}
    /**
     * Wiele maszyn i pojazdów przypisanych do zadania przez tabelę pośrednią machine_task
     */
    public function machines(): BelongsToMany
    {
        return $this->belongsToMany(Machine::class, 'machine_task')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}