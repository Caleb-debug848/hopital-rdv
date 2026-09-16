<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'name',
        'email',
        'telephone',
        'role',
        'password',
    ];

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

    protected static function booted()
    {
        static::saving(function ($user) {
            if (empty($user->name)) {
                $user->name = trim(($user->prenom ?? '') . ' ' . ($user->nom ?? ''));
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMedecin(): bool
    {
        return $this->role === 'medecin';
    }

    public function isSecretaire(): bool
    {
        return in_array($this->role, ['secretaire', 'admin']);
    }

    public function isPatient(): bool
    {
        return $this->role === 'patient';
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function medecin(): HasOne
    {
        return $this->hasOne(Medecin::class);
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function unreadNotificationsCount(): int
    {
        return $this->userNotifications()->where('lu', false)->count();
    }
}
