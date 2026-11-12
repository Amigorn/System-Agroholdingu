<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'status', 'description'];

    // Historia zadań, w których maszyna była używana jako maszyna towarzysząca
    public function tasksAsMachine(): HasMany
    {
        return $this->hasMany(Task::class, 'machine_id');
    }

    // Jeśli ciągniki i maszyny są w tej samej tabeli machines:
    public function tasksAsTractor(): HasMany
    {
        return $this->hasMany(Task::class, 'tractor_id');
    }
}