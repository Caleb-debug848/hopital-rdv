<?php

namespace App\Services;

use App\Models\Disponibilite;
use App\Models\Indisponibilite;
use App\Models\ListeAttente;
use App\Models\Medecin;
use App\Models\Notification;
use App\Models\RendezVous;
use Carbon\Carbon;

class AppointmentSlotService
{
    /**
     * Obtenir les créneaux pour un médecin et une date donnés.
     */
    public function getSlotsForDoctorAndDate(Medecin $medecin, string $dateString): array
    {
        $date = Carbon::parse($dateString);
        $joursFr = [
            1 => 'lundi',
            2 => 'mardi',
            3 => 'mercredi',
            4 => 'jeudi',
            5 => 'vendredi',
            6 => 'samedi',
            7 => 'dimanche',
        ];
        $jourSemaine = $joursFr[$date->dayOfWeekIso] ?? 'lundi';

        // 1. Vérifier si le médecin est actif
        if ($medecin->statut !== 'actif') {
            return [
                'status' => 'inactive',
                'message' => 'Ce praticien n\'est pas en service actuellement.',
                'slots' => [],
                'is_fully_booked' => false,
            ];
        }

        // 2. Vérifier les indisponibilités / congés
        $estIndisponible = Indisponibilite::where('medecin_id', $medecin->id)
            ->whereDate('date_debut', '<=', $date)
            ->whereDate('date_fin', '>=', $date)
            ->first();

        if ($estIndisponible) {
            return [
                'status' => 'indisponible',
                'message' => 'Le médecin est indisponible à cette date' . ($estIndisponible->motif ? ' (' . $estIndisponible->motif . ')' : '') . '.',
                'slots' => [],
                'is_fully_booked' => false,
            ];
        }

        // 3. Récupérer les disponibilités pour ce jour de semaine
        $disponibilites = Disponibilite::where('medecin_id', $medecin->id)
            ->where('jour_semaine', $jourSemaine)
            ->where('is_active', true)
            ->get();

        if ($disponibilites->isEmpty()) {
            return [
                'status' => 'no_schedule',
                'message' => 'Le médecin ne consulte pas les ' . $jourSemaine . 's.',
                'slots' => [],
                'is_fully_booked' => false,
            ];
        }

        // 4. Récupérer les rendez-vous existants pour cette date (hors annulés)
        $rendezVousExistants = RendezVous::where('medecin_id', $medecin->id)
            ->whereDate('date_rdv', $date)
            ->where('statut', '!=', 'annule')
            ->pluck('heure_rdv')
            ->map(function ($h) {
                return substr($h, 0, 5);
            })
            ->toArray();

        // 5. Générer les créneaux
        $slots = [];
        $now = Carbon::now();
        $isToday = $date->isToday();

        foreach ($disponibilites as $dispo) {
            $debut = Carbon::createFromFormat('H:i:s', $dispo->heure_debut);
            $fin = Carbon::createFromFormat('H:i:s', $dispo->heure_fin);
            $duree = $dispo->duree_creneau > 0 ? $dispo->duree_creneau : 30;

            $current = $debut->copy();
            while ($current->lt($fin)) {
                $timeString = $current->format('H:i');
                $isBooked = in_array($timeString, $rendezVousExistants);
                $isPast = $isToday && $current->format('H:i:s') <= $now->format('H:i:s');

                $status = 'disponible';
                if ($isBooked) {
                    $status = 'reserve';
                } elseif ($isPast) {
                    $status = 'passe';
                }

                $slots[] = [
                    'heure' => $timeString,
                    'heure_fin' => $current->copy()->addMinutes($duree)->format('H:i'),
                    'statut' => $status,
                    'disponible' => ($status === 'disponible'),
                ];

                $current->addMinutes($duree);
            }
        }

        $availableCount = count(array_filter($slots, fn($s) => $s['statut'] === 'disponible'));
        $totalSlots = count($slots);
        $isFullyBooked = ($totalSlots > 0 && $availableCount === 0);

        return [
            'status' => 'success',
            'jour_semaine' => $jourSemaine,
            'date_formattee' => $date->translatedFormat('l d F Y'),
            'slots' => $slots,
            'available_count' => $availableCount,
            'total_count' => $totalSlots,
            'is_fully_booked' => $isFullyBooked,
        ];
    }

    /**
     * Traiter la liste d'attente lors de l'annulation d'un rendez-vous.
     */
    public function handleAppointmentCancellation(RendezVous $rdv): void
    {
        // Trouver les patients en attente pour ce médecin et cette date
        $enAttente = ListeAttente::where('medecin_id', $rdv->medecin_id)
            ->whereDate('date_souhaitee', $rdv->date_rdv)
            ->where('statut', 'en_attente')
            ->with(['patient.user', 'medecin'])
            ->get();

        foreach ($enAttente as $item) {
            if ($item->patient && $item->patient->user) {
                Notification::create([
                    'user_id' => $item->patient->user_id,
                    'type' => 'liste_attente',
                    'titre' => 'Créneau disponible : consultation libérée',
                    'message' => 'Un créneau s\'est libéré avec le ' . $rdv->medecin->nom_complet . ' pour le ' . Carbon::parse($rdv->date_rdv)->format('d/m/Y') . ' à ' . substr($rdv->heure_rdv, 0, 5) . '. Réservez rapidement votre consultation.',
                    'lien' => route('patient.rendez-vous.create', [
                        'specialite_id' => $rdv->specialite_id,
                        'medecin_id' => $rdv->medecin_id,
                        'date' => Carbon::parse($rdv->date_rdv)->format('Y-m-d')
                    ]),
                    'lu' => false,
                ]);

                $item->update([
                    'statut' => 'notifie',
                    'date_notification' => now(),
                ]);
            }
        }
    }
}
