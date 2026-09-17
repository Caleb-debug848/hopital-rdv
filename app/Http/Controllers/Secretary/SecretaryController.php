<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Medecin;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Specialite;
use App\Models\User;
use App\Services\AppointmentSlotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SecretaryController extends Controller
{
    protected AppointmentSlotService $slotService;

    public function __construct(AppointmentSlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    public function guichet(Request $request)
    {
        $selectedDate = $request->query('date', Carbon::today()->toDateString());
        $selectedMedecinId = $request->query('medecin_id');
        $selectedStatut = $request->query('statut');
        $search = $request->query('search');

        $date = Carbon::parse($selectedDate);

        $query = RendezVous::whereDate('date_rdv', $date)
            ->with(['patient.user', 'medecin.user', 'specialite']);

        if ($selectedMedecinId) {
            $query->where('medecin_id', $selectedMedecinId);
        }

        if ($selectedStatut) {
            $query->where('statut', $selectedStatut);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_rdv', 'like', "%{$search}%")
                    ->orWhereHas('patient.user', function ($qu) use ($search) {
                        $qu->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%")
                            ->orWhere('telephone', 'like', "%{$search}%");
                    });
            });
        }

        $rendezVous = $query->orderBy('heure_rdv', 'asc')->get();

        // Statistiques de la journée
        $allDayRdv = RendezVous::whereDate('date_rdv', $date)->get();
        $stats = [
            'total' => $allDayRdv->count(),
            'confirme' => $allDayRdv->where('statut', 'confirme')->count(),
            'arrive' => $allDayRdv->where('statut', 'arrive')->count(),
            'en_attente' => $allDayRdv->where('statut', 'en_attente')->count(),
            'termine' => $allDayRdv->where('statut', 'termine')->count(),
            'absent' => $allDayRdv->where('statut', 'absent')->count(),
            'annule' => $allDayRdv->where('statut', 'annule')->count(),
        ];

        $medecins = Medecin::where('statut', 'actif')->with('user')->get();
        $specialites = Specialite::where('is_active', true)->get();
        $patients = Patient::with('user')->get();

        return view('secretary.guichet', compact(
            'rendezVous',
            'stats',
            'medecins',
            'specialites',
            'patients',
            'selectedDate',
            'selectedMedecinId',
            'selectedStatut',
            'search',
            'date'
        ));
    }

    public function updateStatus(Request $request, RendezVous $rendezVous)
    {
        $validated = $request->validate([
            'statut' => 'required|in:en_attente,confirme,arrive,termine,absent,annule',
            'notes_annulation' => 'nullable|string|max:500',
        ]);

        $updateData = ['statut' => $validated['statut']];

        if ($validated['statut'] === 'arrive') {
            $updateData['date_arrivee'] = now();
        } elseif ($validated['statut'] === 'termine') {
            $updateData['date_effectue'] = now();
        } elseif ($validated['statut'] === 'annule') {
            $updateData['annule_par'] = 'secretaire';
            $updateData['notes_annulation'] = $validated['notes_annulation'] ?? 'Annulé par le secrétariat.';
        }

        $rendezVous->update($updateData);

        // Notifier le patient
        if ($rendezVous->patient && $rendezVous->patient->user) {
            Notification::create([
                'user_id' => $rendezVous->patient->user_id,
                'type' => $validated['statut'],
                'titre' => 'Mise à jour RDV ' . $rendezVous->reference_rdv,
                'message' => 'Statut du rendez-vous mis à jour au secrétariat : ' . $rendezVous->statut_badge['label'],
                'lien' => route('patient.rendez-vous.show', $rendezVous->id),
                'lu' => false,
            ]);
        }

        if ($validated['statut'] === 'annule') {
            $this->slotService->handleAppointmentCancellation($rendezVous);
        }

        // Traçabilité médico-légale dans le journal d'audit
        $actionMap = [
            'arrive' => 'POINTAGE_ARRIVEE',
            'termine' => 'CONSULTATION_TERMINEE',
            'absent' => 'PATIENT_ABSENT',
            'annule' => 'ANNULATION_RDV',
            'confirme' => 'CONFIRMATION_RDV',
        ];
        $secretaireNom = \Illuminate\Support\Facades\Auth::user()->full_name;
        $patientNom = $rendezVous->patient?->user?->full_name ?? 'le patient';
        $desc = ($validated['statut'] === 'arrive')
            ? "{$secretaireNom} a pointé l'arrivée de {$patientNom} pour la consultation {$rendezVous->reference_rdv}"
            : "{$secretaireNom} a mis à jour le statut de {$rendezVous->reference_rdv} vers : " . ucfirst($validated['statut']);

        \App\Services\AuditLogger::log(
            $actionMap[$validated['statut']] ?? 'STATUT_CHANGE',
            $desc,
            [
                'rendez_vous_id' => $rendezVous->id,
                'reference' => $rendezVous->reference_rdv,
                'statut' => $validated['statut'],
                'patient_id' => $rendezVous->patient_id,
            ]
        );

        return back()->with('success', 'Rendez-vous mis à jour : statut "' . $rendezVous->statut_badge['label'] . '"');
    }

    public function createAppointmentDesk(Request $request)
    {
        $validated = $request->validate([
            'patient_type' => 'required|in:existing,new',
            'patient_id' => 'required_if:patient_type,existing|nullable|exists:patients,id',
            'nom' => 'required_if:patient_type,new|nullable|string|max:100',
            'prenom' => 'required_if:patient_type,new|nullable|string|max:100',
            'telephone' => 'required_if:patient_type,new|nullable|string|max:50',
            'email' => 'nullable|email|max:191',
            'medecin_id' => 'required|exists:medecins,id',
            'specialite_id' => 'required|exists:specialites,id',
            'date_rdv' => 'required|date',
            'heure_rdv' => 'required|string',
            'motif' => 'nullable|string|max:255',
        ]);

        if ($validated['patient_type'] === 'new') {
            $email = !empty($validated['email']) ? $validated['email'] : 'patient.' . time() . '@hopital-rdv.local';
            $user = User::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'telephone' => $validated['telephone'],
                'email' => $email,
                'role' => 'patient',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);

            $patient = Patient::create([
                'user_id' => $user->id,
            ]);
        } else {
            $patient = Patient::findOrFail($validated['patient_id']);
        }

        $rdv = RendezVous::create([
            'patient_id' => $patient->id,
            'medecin_id' => $validated['medecin_id'],
            'specialite_id' => $validated['specialite_id'],
            'date_rdv' => $validated['date_rdv'],
            'heure_rdv' => $validated['heure_rdv'],
            'statut' => 'confirme',
            'motif' => $validated['motif'] ?? 'Prise de rendez-vous au guichet',
        ]);

        // Traçabilité de la création de RDV au guichet
        \App\Services\AuditLogger::log(
            'CREATION_RDV',
            \Illuminate\Support\Facades\Auth::user()->full_name . " a créé un rendez-vous au guichet ({$rdv->reference_rdv}) pour {$patient->user->full_name}",
            [
                'rendez_vous_id' => $rdv->id,
                'reference' => $rdv->reference_rdv,
                'patient_id' => $patient->id,
                'medecin_id' => $validated['medecin_id'],
            ]
        );

        return back()->with('success', 'Rendez-vous ' . $rdv->reference_rdv . ' enregistré avec succès au guichet !');
    }
}
