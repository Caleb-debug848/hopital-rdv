<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Enregistrer une trace d'audit médico-légale
     */
    public static function log(string $action, string $description, ?array $details = null, ?User $user = null): AuditLog
    {
        $currentUser = $user ?? Auth::user();

        return AuditLog::create([
            'user_id' => $currentUser?->id,
            'user_name' => $currentUser ? $currentUser->full_name : 'Système Automatisé',
            'user_role' => $currentUser ? $currentUser->role : 'system',
            'action' => $action,
            'description' => $description,
            'ip_address' => request() ? request()->ip() : null,
            'details' => $details,
        ]);
    }
}
