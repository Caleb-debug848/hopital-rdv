<?php

namespace App\Services;

use App\Mail\AppointmentConfirmationMail;
use App\Mail\AppointmentReminderMail;
use App\Models\Notification;
use App\Models\RendezVous;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReminderService
{
    /**
     * Envoie un rappel officiel par E-mail réel et prépare la convocation WhatsApp.
     *
     * @param RendezVous $rdv
     * @param string $type ('both', 'email', 'whatsapp')
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

        $emailSent = false;
        $emailError = null;

        // 1. Envoi de l'Email Réel avec Mailable médical haut de gamme
        if (($type === 'both' || $type === 'email') && !empty($user->email)) {
            try {
                Mail::to($user->email)->send(new AppointmentReminderMail($rdv));
                $emailSent = true;
                Log::info("EMAIL_REMINDER_SUCCESS: Envoyé avec succès à {$user->email} pour RDV #{$rdv->reference_rdv}");
            } catch (\Throwable $e) {
                $emailError = $e->getMessage();
                Log::warning("EMAIL_REMINDER_FAILED: Échec de l'envoi à {$user->email} ({$emailError}) - Basculement log sécurisé.");
            }
        }

        // 2. Génération du message et de l'URL WhatsApp (Strictement sans emoji)
        $whatsappMessage = CommunicationHelper::formatWhatsAppMessage($rdv);
        $whatsappUrl = CommunicationHelper::generateWhatsAppUrl($user->telephone, $whatsappMessage);

        // 3. Notification interne dans l'espace patient
        Notification::create([
            'user_id' => $user->id,
            'type' => 'rappel',
            'titre' => 'Rappel de consultation : ' . $rdv->reference_rdv,
            'message' => "Rappel officiel envoyé pour votre consultation avec le Dr. {$doctorName} le {$dateFr} à {$heure}.",
            'lien' => route('patient.rendez-vous.show', $rdv->id),
            'lu' => false,
        ]);

        // 4. Traçabilité dans le journal d'audit
        AuditLogger::log(
            'RAPPEL_CONSULTATION',
            "Rappel officiel envoyé à {$user->full_name} ({$user->email}) pour {$rdv->reference_rdv}",
            [
                'rendez_vous_id' => $rdv->id,
                'reference' => $rdv->reference_rdv,
                'email' => $user->email,
                'telephone' => $user->telephone,
                'email_sent' => $emailSent,
            ]
        );

        return [
            'status' => 'success',
            'email_sent' => $emailSent,
            'email_error' => $emailError,
            'recipient_phone' => $user->telephone,
            'recipient_email' => $user->email,
            'whatsapp_url' => $whatsappUrl,
            'whatsapp_message' => $whatsappMessage,
            'sent_at' => now()->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Envoie la confirmation officielle de rendez-vous par e-mail au patient.
     *
     * @param RendezVous $rdv
     * @return bool
     */
    public function sendConfirmation(RendezVous $rdv): bool
    {
        $patient = $rdv->patient;
        $user = $patient ? $patient->user : null;

        if (!$user || empty($user->email)) {
            return false;
        }

        try {
            Mail::to($user->email)->send(new AppointmentConfirmationMail($rdv));
            Log::info("EMAIL_CONFIRMATION_SUCCESS: Confirmation envoyée à {$user->email} pour RDV #{$rdv->reference_rdv}");
            return true;
        } catch (\Throwable $e) {
            Log::warning("EMAIL_CONFIRMATION_FAILED: Erreur pour {$user->email} ({$e->getMessage()})");
            return false;
        }
    }
}
