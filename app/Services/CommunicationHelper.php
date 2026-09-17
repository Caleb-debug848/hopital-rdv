<?php

namespace App\Services;

use App\Models\RendezVous;
use Carbon\Carbon;

class CommunicationHelper
{
    /**
     * Détermine la salutation contextuelle selon l'heure locale.
     * « Bonjour » entre 05h00 et 17h59, « Bonsoir » entre 18h00 et 04h59.
     *
     * @param Carbon|null $time
     * @return string
     */
    public static function salutation(?Carbon $time = null): string
    {
        $now = $time ?? Carbon::now();
        $hour = (int) $now->format('H');

        if ($hour >= 18 || $hour < 5) {
            return 'Bonsoir';
        }

        return 'Bonjour';
    }

    /**
     * Formate un message officiel de convocation et de rappel WhatsApp.
     * STRICTEMENT AUCUN EMOJI : Style clinique et hospitalier institutionnel.
     *
     * @param RendezVous $rdv
     * @return string
     */
    public static function formatWhatsAppMessage(RendezVous $rdv): string
    {
        $patient = $rdv->patient;
        $patientUser = $patient ? $patient->user : null;
        $patientName = $patientUser ? $patientUser->full_name : 'Patient';

        $medecin = $rdv->medecin;
        $doctorName = $medecin ? $medecin->nom_complet : 'Praticien';
        $specialiteName = $rdv->specialite ? $rdv->specialite->nom : 'Consultation Generale';
        $bureau = ($medecin && $medecin->bureau) ? $medecin->bureau : 'Service Central des Consultations';

        $dateFr = Carbon::parse($rdv->date_rdv)->translatedFormat('l d F Y');
        $heure = substr($rdv->heure_rdv, 0, 5);

        $salutation = self::salutation();
        $urlConvocation = route('patient.rendez-vous.show', $rdv->id);

        $ref = str_starts_with($rdv->reference_rdv, '#') ? $rdv->reference_rdv : '#' . $rdv->reference_rdv;

        $doctorDisplay = str_starts_with($doctorName, 'Dr') ? $doctorName : 'Dr. ' . $doctorName;

        $lines = [
            "*HOPITAL RDV — CONVOCATION ET RAPPEL MEDICAL*",
            "",
            "{$salutation} *{$patientName}*,",
            "",
            "Nous vous rappelons votre consultation medicale programmee selon les modalites suivantes :",
            "",
            "- *Reference :* {$ref}",
            "- *Date :* {$dateFr}",
            "- *Heure :* {$heure}",
            "- *Praticien :* {$doctorDisplay}",
            "- *Specialite :* {$specialiteName}",
            "- *Lieu :* {$bureau}",
            "",
            "*Consulter votre convocation officielle en ligne :*",
            $urlConvocation,
            "",
            "*Consigne :* Merci de vous presenter a l'accueil 15 minutes avant l'heure fixee, muni de votre piece d'identite.",
            "",
            "_En cas d'empechement, merci d'annuler votre consultation depuis votre espace patient afin de liberer le creneau._",
            "",
            "Direction des Consultations Medicales — Hopital RDV",
        ];

        return implode("\n", $lines);
    }

    /**
     * Nettoie et normalise le numéro de téléphone pour WhatsApp.
     * Ex: '+237 695 07 34 77' -> '237695073477'
     *
     * @param string|null $phone
     * @return string
     */
    public static function formatPhoneNumber(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Supprimer espaces, tirets, parenthèses et signes plus
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // Si le numéro commence par un zéro local à 9 chiffres (ex: 695073477 au Cameroun), préfixer par 237
        if (strlen($clean) === 9 && str_starts_with($clean, '6')) {
            $clean = '237' . $clean;
        }

        return $clean;
    }

    /**
     * Génère l'URL directe pour ouvrir l'application WhatsApp avec message pré-rempli.
     *
     * @param string|null $phone
     * @param string $message
     * @return string
     */
    public static function generateWhatsAppUrl(?string $phone, string $message): string
    {
        $cleanPhone = self::formatPhoneNumber($phone);
        $encodedMessage = rawurlencode($message);

        if (!empty($cleanPhone)) {
            return "https://wa.me/{$cleanPhone}?text={$encodedMessage}";
        }

        return "https://api.whatsapp.com/send?text={$encodedMessage}";
    }
}
