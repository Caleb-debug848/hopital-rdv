<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RendezVous extends Model
{
    use HasFactory;

    protected $table = 'rendez_vous';

    protected $fillable = [
        'reference_rdv',
        'patient_id',
        'medecin_id',
        'specialite_id',
        'date_rdv',
        'heure_rdv',
        'heure_fin_rdv',
        'statut',
        'motif',
        'notes_annulation',
        'annule_par',
        'date_arrivee',
        'date_effectue',
    ];

    protected function casts(): array
    {
        return [
            'date_rdv' => 'date',
            'date_arrivee' => 'datetime',
            'date_effectue' => 'datetime',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($rdv) {
            if (empty($rdv->reference_rdv)) {
                $rdv->reference_rdv = '#RDV-' . str_pad((string) mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function medecin(): BelongsTo
    {
        return $this->belongsTo(Medecin::class);
    }

    public function specialite(): BelongsTo
    {
        return $this->belongsTo(Specialite::class);
    }

    public function getHeureRdvFormatteeAttribute(): string
    {
        return substr($this->heure_rdv, 0, 5);
    }

    public function getStatutBadgeAttribute(): array
    {
        return match ($this->statut) {
            'en_attente' => [
                'label' => 'En attente',
                'bg' => 'bg-amber-100 text-amber-800 border-amber-200',
                'dot' => 'bg-amber-500',
                'icon' => 'clock'
            ],
            'confirme' => [
                'label' => 'Confirmé',
                'bg' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'dot' => 'bg-emerald-500',
                'icon' => 'check-circle'
            ],
            'arrive' => [
                'label' => 'Patient arrivé',
                'bg' => 'bg-blue-100 text-blue-800 border-blue-200',
                'dot' => 'bg-blue-500',
                'icon' => 'user-check'
            ],
            'termine' => [
                'label' => 'Consultation effectuée',
                'bg' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                'dot' => 'bg-indigo-500',
                'icon' => 'check-check'
            ],
            'annule' => [
                'label' => 'Annulé',
                'bg' => 'bg-rose-100 text-rose-800 border-rose-200',
                'dot' => 'bg-rose-500',
                'icon' => 'x-circle'
            ],
            'absent' => [
                'label' => 'Patient absent',
                'bg' => 'bg-slate-100 text-slate-700 border-slate-200',
                'dot' => 'bg-slate-400',
                'icon' => 'user-x'
            ],
            default => [
                'label' => ucfirst($this->statut),
                'bg' => 'bg-gray-100 text-gray-800 border-gray-200',
                'dot' => 'bg-gray-400',
                'icon' => 'info'
            ],
        };
    }
}
