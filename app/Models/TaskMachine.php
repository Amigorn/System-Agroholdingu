<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskMachine extends Pivot
{
    protected $table = 'task_machines';

    protected $fillable = [
        'task_id',
        'tractor_id',
        'machine_id',
        'user_id',
        'notes',
    ];

    // Kluczowa relacja do zadania, z której ciągniemy typ, status i daty
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function tractor(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'tractor_id');
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'machine_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}