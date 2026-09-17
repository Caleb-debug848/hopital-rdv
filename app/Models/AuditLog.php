<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'description',
        'ip_address',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionBadgeAttribute(): array
    {
        return match ($this->action) {
            'POINTAGE_ARRIVEE' => [
                'label' => 'Arrivée Pointée',
                'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                'icon' => 'user-check',
            ],
            'CONSULTATION_TERMINEE' => [
                'label' => 'Consultation Validée',
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'icon' => 'check-circle-2',
            ],
            'PATIENT_ABSENT' => [
                'label' => 'Absence Déclarée',
                'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
                'icon' => 'user-x',
            ],
            'CREATION_RDV' => [
                'label' => 'Prise de Rendez-vous',
                'bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'icon' => 'calendar-plus',
            ],
            'ANNULATION_RDV' => [
                'label' => 'Annulation Consultation',
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'icon' => 'calendar-x',
            ],
            'PARAMETRES_MODIFIES' => [
                'label' => 'Paramètres Système',
                'bg' => 'bg-purple-50 text-purple-700 border-purple-200',
                'icon' => 'settings',
            ],
            default => [
                'label' => ucwords(str_replace('_', ' ', strtolower($this->action))),
                'bg' => 'bg-slate-100 text-slate-700 border-slate-200',
                'icon' => 'activity',
            ],
        };
    }
}
