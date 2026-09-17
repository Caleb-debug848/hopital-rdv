<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cabinet extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'batiment',
        'etage',
        'capacite',
        'equipements',
        'is_actif',
    ];

    protected function casts(): array
    {
        return [
            'is_actif' => 'boolean',
            'capacite' => 'integer',
        ];
    }

    public function medecins(): HasMany
    {
        return $this->hasMany(Medecin::class);
    }

    public function getLocalisationCompleteAttribute(): string
    {
        return "{$this->nom} — {$this->batiment}, {$this->etage}";
    }
}
