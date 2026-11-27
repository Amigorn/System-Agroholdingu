<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskMachine extends Model
{
    protected $table = 'task_machines';

    protected $fillable = [
        'task_id',
        'tractor_id',
        'machine_id',
        'user_id',
        'notes',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
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