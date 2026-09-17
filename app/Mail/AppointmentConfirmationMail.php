<?php

namespace App\Mail;

use App\Models\RendezVous;
use App\Services\CommunicationHelper;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public RendezVous $rdv;
    public string $salutation;
    public string $dateFr;
    public string $heure;
    public string $patientName;
    public string $doctorName;
    public string $specialiteName;
    public string $bureau;

    /**
     * Create a new message instance.
     */
    public function __construct(RendezVous $rdv)
    {
        $this->rdv = $rdv;

        $patientUser = $rdv->patient ? $rdv->patient->user : null;
        $this->patientName = $patientUser ? $patientUser->full_name : 'Patient';

        $medecin = $rdv->medecin;
        $this->doctorName = $medecin ? $medecin->nom_complet : 'Praticien';
        $this->specialiteName = $rdv->specialite ? $rdv->specialite->nom : 'Consultation Generale';
        $this->bureau = ($medecin && $medecin->bureau) ? $medecin->bureau : 'Service Central des Consultations';

        $this->dateFr = Carbon::parse($rdv->date_rdv)->translatedFormat('l d F Y');
        $this->heure = substr($rdv->heure_rdv, 0, 5);
        $this->salutation = CommunicationHelper::salutation();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Hôpital RDV] Confirmation de votre rendez-vous médical - Réf: {$this->rdv->reference_rdv}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment_confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
