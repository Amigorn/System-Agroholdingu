<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- Sprawdź czy jest ten import
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory; // <-- Sprawdź czy to na pewno tu siedzi

    protected $fillable = [
        'type', 
        'planned_at', 
        'completed_at', 
        'status', 
        'description', 
        'user_id', 
        'machine_id', 
        'field_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }
}