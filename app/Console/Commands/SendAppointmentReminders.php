<?php

namespace App\Console\Commands;

use App\Models\RendezVous;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rdv:send-reminders {--date= : Date cible YYYY-MM-DD (par défaut demain)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie les rappels officiels par e-mail et prépare les alertes pour les consultations du lendemain';

    /**
     * Execute the console command.
     */
    public function handle(ReminderService $reminderService): int
    {
        $dateOption = $this->option('date');
        $targetDate = $dateOption ? Carbon::parse($dateOption)->toDateString() : Carbon::tomorrow()->toDateString();
        $dateFr = Carbon::parse($targetDate)->translatedFormat('l d F Y');

        $this->info("Traitement des rappels de consultation pour le {$dateFr} ({$targetDate})...");

        $rendezVous = RendezVous::where('date_rdv', $targetDate)
            ->whereIn('statut', ['confirme', 'en_attente'])
            ->with(['patient.user', 'medecin.user', 'specialite'])
            ->get();

        if ($rendezVous->isEmpty()) {
            $this->comment("Aucune consultation prévue pour le {$targetDate}.");
            return Command::SUCCESS;
        }

        $sentCount = 0;
        $failedCount = 0;

        foreach ($rendezVous as $rdv) {
            $patientName = $rdv->patient?->user?->full_name ?? 'Inconnu';
            $ref = str_starts_with($rdv->reference_rdv, '#') ? $rdv->reference_rdv : '#' . $rdv->reference_rdv;
            $this->line("Envoi du rappel pour {$ref} ({$patientName})...");

            $result = $reminderService->sendReminder($rdv, 'both');

            if ($result['status'] === 'success') {
                $sentCount++;
            } else {
                $failedCount++;
                $this->warn("Échec pour {$ref} : " . ($result['message'] ?? 'Erreur'));
            }
        }

        $this->info("Rappels terminés : {$sentCount} envoyés avec succès, {$failedCount} échecs.");

        return Command::SUCCESS;
    }
}
