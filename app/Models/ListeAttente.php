<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListeAttente extends Model
{
    use HasFactory;

    protected $table = 'listes_attente';

    protected $fillable = [
        'patient_id',
        'medecin_id',
        'specialite_id',
        'date_souhaitee',
        'plage_horaire',
        'statut',
        'date_notification',
    ];

    protected function casts(): array
    {
        return [
            'date_souhaitee' => 'date',
            'date_notification' => 'datetime',
        ];
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
}
