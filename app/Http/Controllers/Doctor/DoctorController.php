<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Indisponibilite;
use App\Models\Medecin;
use App\Models\Notification;
use App\Models\RendezVous;
use App\Services\AppointmentSlotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    protected AppointmentSlotService $slotService;

    public function __construct(AppointmentSlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    private function getMedecin(): Medecin
    {
        $user = Auth::user();
        $medecin = $user->medecin;
        if (!$medecin) {
            abort(403, 'Profil médecin non configuré.');
        }
        return $medecin;
    }

    public function dashboard()
    {
        $medecin = $this->getMedecin();
        $today = Carbon::today();
        $now = Carbon::now();

        $rdvAujourdhui = RendezVous::where('medecin_id', $medecin->id)
            ->whereDate('date_rdv', $today)
            ->with(['patient.user', 'specialite'])
            ->orderBy('heure_rdv', 'asc')
            ->get();

        $prochainPatient = RendezVous::where('medecin_id', $medecin->id)
            ->whereDate('date_rdv', $today)
            ->whereIn('statut', ['confirme', 'arrive', 'en_attente'])
            ->where('heure_rdv', '>=', $now->copy()->subMinutes(15)->format('H:i:s'))
            ->with(['patient.user', 'specialite'])
            ->orderBy('heure_rdv', 'asc')
            ->first();

        $stats = [
            'total_aujourdhui' => $rdvAujourdhui->count(),
            'confirmes_aujourdhui' => $rdvAujourdhui->where('statut', 'confirme')->count(),
            'arrives_aujourdhui' => $rdvAujourdhui->where('statut', 'arrive')->count(),
            'effectues_aujourdhui' => $rdvAujourdhui->where('statut', 'termine')->count(),
            'a_venir_semaine' => RendezVous::where('medecin_id', $medecin->id)
                ->whereBetween('date_rdv', [$today, $today->copy()->endOfWeek()])
                ->whereIn('statut', ['confirme', 'en_attente'])
                ->count(),
        ];

        return view('doctor.dashboard', compact('medecin', 'rdvAujourdhui', 'prochainPatient', 'stats'));
    }

    public function planning(Request $request)
    {
        $medecin = $this->getMedecin();
        $selectedDate = $request->query('date', Carbon::today()->toDateString());
        $date = Carbon::parse($selectedDate);

        $rdvs = RendezVous::where('medecin_id', $medecin->id)
            ->whereDate('date_rdv', $date)
            ->with(['patient.user', 'specialite'])
            ->orderBy('heure_rdv', 'asc')
            ->get();

        $indisponibilites = Indisponibilite::where('medecin_id', $medecin->id)
            ->whereDate('date_debut', '<=', $date)
            ->whereDate('date_fin', '>=', $date)
            ->get();

        $mesIndisponibilites = Indisponibilite::where('medecin_id', $medecin->id)
            ->whereDate('date_fin', '>=', Carbon::today())
            ->orderBy('date_debut', 'asc')
            ->get();

        return view('doctor.planning', compact('medecin', 'selectedDate', 'date', 'rdvs', 'indisponibilites', 'mesIndisponibilites'));
    }

    public function updateStatus(Request $request, RendezVous $rendezVous)
    {
        $medecin = $this->getMedecin();
        if ($rendezVous->medecin_id !== $medecin->id && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'statut' => 'required|in:confirme,arrive,termine,absent,annule',
            'notes_annulation' => 'nullable|string|max:500',
        ]);

        $updateData = ['statut' => $validated['statut']];

        if ($validated['statut'] === 'arrive') {
            $updateData['date_arrivee'] = now();
        } elseif ($validated['statut'] === 'termine') {
            $updateData['date_effectue'] = now();
        } elseif ($validated['statut'] === 'annule') {
            $updateData['annule_par'] = 'medecin';
            $updateData['notes_annulation'] = $validated['notes_annulation'] ?? 'Annulé par le médecin.';
        }

        $rendezVous->update($updateData);

        // Notifier le patient
        $messages = [
            'confirme' => 'Votre rendez-vous a été confirmé par le ' . $medecin->nom_complet,
            'arrive' => 'Votre arrivée a été enregistrée au secrétariat.',
            'termine' => 'Consultation terminée. Merci de votre visite.',
            'absent' => 'Vous avez été marqué comme absent à votre consultation.',
            'annule' => 'Votre rendez-vous a été annulé par le médecin : ' . ($validated['notes_annulation'] ?? ''),
        ];

        Notification::create([
            'user_id' => $rendezVous->patient->user_id,
            'type' => $validated['statut'],
            'titre' => 'Mise à jour rendez-vous ' . $rendezVous->reference_rdv,
            'message' => $messages[$validated['statut']] ?? 'Statut mis à jour',
            'lien' => route('patient.rendez-vous.show', $rendezVous->id),
            'lu' => false,
        ]);

        if ($validated['statut'] === 'annule') {
            $this->slotService->handleAppointmentCancellation($rendezVous);
        }

        return back()->with('success', 'Statut du rendez-vous mis à jour avec succès.');
    }

    public function storeIndisponibilite(Request $request)
    {
        $medecin = $this->getMedecin();

        $validated = $request->validate([
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'nullable|string|max:255',
        ]);

        Indisponibilite::create([
            'medecin_id' => $medecin->id,
            'date_debut' => $validated['date_debut'],
            'date_fin' => $validated['date_fin'],
            'motif' => $validated['motif'] ?? 'Congé / Indisponibilité',
        ]);

        return back()->with('success', 'Période d\'indisponibilité enregistrée.');
    }
}
