<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'machine_id', 
        'tractor_id', // <-- Dopisane pole dla ciągnika
        'field_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'machine_id');
    }

    public function tractor(): BelongsTo
    {
        return $this->belongsTo(Machine::class, 'tractor_id'); // <-- Dopisana relacja dla ciągnika
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }
}