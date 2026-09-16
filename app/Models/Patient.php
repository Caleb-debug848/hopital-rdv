<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'numero_patient',
        'date_naissance',
        'sexe',
        'contact_urgence',
        'adresse',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($patient) {
            if (empty($patient->numero_patient)) {
                $patient->numero_patient = 'PAT-' . date('Y') . '-' . str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rendezVous(): HasMany
    {
        return $this->hasMany(RendezVous::class)->orderBy('date_rdv', 'desc')->orderBy('heure_rdv', 'desc');
    }

    public function listesAttente(): HasMany
    {
        return $this->hasMany(ListeAttente::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_naissance ? (int) $this->date_naissance->age : null;
    }
}
