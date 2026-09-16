<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReminderService
{
    /**
     * Simule l'envoi d'un rappel par SMS et Email pour un rendez-vous donné.
     *
     * @param RendezVous $rdv
     * @param string $type ('sms', 'email', 'both')
     * @return array
     */
    public function sendReminder(RendezVous $rdv, string $type = 'both'): array
    {
        $patient = $rdv->patient;
        $user = $patient ? $patient->user : null;
        $medecin = $rdv->medecin;

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'Impossible d\'envoyer le rappel : patient introuvable.',
            ];
        }

        $dateFr = Carbon::parse($rdv->date_rdv)->translatedFormat('l d F Y');
        $heure = substr($rdv->heure_rdv, 0, 5);
        $doctorName = $medecin ? $medecin->nom_complet : 'votre praticien';

        $smsContent = "Hôpital RDV : Rappel de votre consultation avec {$doctorName} le {$dateFr} à {$heure}. Réf: {$rdv->reference_rdv}. En cas d'empêchement, merci d'annuler en ligne.";
        $emailSubject = "Rappel de consultation médicale - Réf: {$rdv->reference_rdv}";

        // Enregistrement dans les logs système
        Log::info("SMS_REMINDER_SIMULATED: Sent to {$user->telephone} -> {$smsContent}");
        Log::info("EMAIL_REMINDER_SIMULATED: Sent to {$user->email} -> {$emailSubject}");

        // Création d'une notification dans l'espace patient
        Notification::create([
            'user_id' => $user->id,
            'type' => 'rappel',
            'titre' => 'Rappel de consultation : ' . $rdv->reference_rdv,
            'message' => "Rappel automatique envoyé par SMS ({$user->telephone}) et Email ({$user->email}) pour votre RDV du {$dateFr} à {$heure}.",
            'lien' => route('patient.rendez-vous.show', $rdv->id),
            'lu' => false,
        ]);

        return [
            'status' => 'success',
            'recipient_phone' => $user->telephone,
            'recipient_email' => $user->email,
            'sms_preview' => $smsContent,
            'email_subject' => $emailSubject,
            'sent_at' => now()->format('d/m/Y H:i:s'),
        ];
    }
}
