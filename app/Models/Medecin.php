<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medecin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'specialite_id',
        'titre',
        'service',
        'bureau',
        'biographie',
        'jours_consultation',
        'heure_debut_defaut',
        'heure_fin_defaut',
        'duree_consultation',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'jours_consultation' => 'array',
            'duree_consultation' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function specialite(): BelongsTo
    {
        return $this->belongsTo(Specialite::class);
    }

    public function disponibilites(): HasMany
    {
        return $this->hasMany(Disponibilite::class);
    }

    public function indisponibilites(): HasMany
    {
        return $this->hasMany(Indisponibilite::class);
    }

    public function rendezVous(): HasMany
    {
        return $this->hasMany(RendezVous::class)->orderBy('date_rdv', 'asc')->orderBy('heure_rdv', 'asc');
    }

    public function listesAttente(): HasMany
    {
        return $this->hasMany(ListeAttente::class);
    }

    public function getNomCompletAttribute(): string
    {
        return ($this->titre ? $this->titre . ' ' : 'Dr ') . ($this->user ? $this->user->prenom . ' ' . $this->user->nom : '');
    }

    public function isActif(): bool
    {
        return $this->statut === 'actif';
    }
}
