<?php

namespace App\Models;

use Filament\Models\Contracts\HasName; // <-- Dodaj tę linię
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements HasName // <-- Dodaj "implements HasName"
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
    ];

    // Ta metoda powie Filamentowi, jak wyświetlić Twoje imię i nazwisko
    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
