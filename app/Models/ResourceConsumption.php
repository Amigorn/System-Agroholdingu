<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceConsumption extends Model
{
    /** @use HasFactory<\Database\Factories\ResourceConsumptionFactory> */
    use HasFactory;

    protected $fillable = [
        'quantity', 
        'task_id', 
        'resource_id',
    ];

    public function task(): BelongsTo 
    { 
        return $this->belongsTo(Task::class, 'task_id'); 
    }

    public function resource(): BelongsTo 
    { 
        return $this->belongsTo(Resource::class, 'resource_id'); 
    } 
}