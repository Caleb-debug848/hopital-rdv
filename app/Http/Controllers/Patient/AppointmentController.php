<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\ListeAttente;
use App\Models\Medecin;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Specialite;
use App\Services\AppointmentSlotService;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    protected AppointmentSlotService $slotService;

    public function __construct(AppointmentSlotService $slotService)
    {
        $this->slotService = $slotService;
    }

    private function getPatient(): Patient
    {
        $user = Auth::user();
        return $user->patient ?? Patient::firstOrCreate(
            ['user_id' => $user->id],
            ['numero_patient' => 'PAT-' . date('Y') . '-' . str_pad((string) mt_rand(1, 99999), 5, '0', STR_PAD_LEFT)]
        );
    }

    public function dashboard()
    {
        $patient = $this->getPatient();
        $today = Carbon::today();

        $prochainRdv = RendezVous::where('patient_id', $patient->id)
            ->where('date_rdv', '>=', $today)
            ->whereIn('statut', ['en_attente', 'confirme', 'arrive'])
            ->with(['medecin.user', 'specialite'])
            ->orderBy('date_rdv', 'asc')
            ->orderBy('heure_rdv', 'asc')
            ->first();

        $rdvAvenir = RendezVous::where('patient_id', $patient->id)
            ->where('date_rdv', '>=', $today)
            ->whereIn('statut', ['en_attente', 'confirme', 'arrive'])
            ->with(['medecin.user', 'specialite'])
            ->orderBy('date_rdv', 'asc')
            ->get();

        $stats = [
            'total' => RendezVous::where('patient_id', $patient->id)->count(),
            'a_venir' => $rdvAvenir->count(),
            'effectues' => RendezVous::where('patient_id', $patient->id)->where('statut', 'termine')->count(),
            'annules' => RendezVous::where('patient_id', $patient->id)->where('statut', 'annule')->count(),
        ];

        $specialites = Specialite::where('is_active', true)->withCount('medecins')->get();
        $notifications = Auth::user()->userNotifications()->latest()->take(5)->get();

        return view('patient.dashboard', compact('patient', 'prochainRdv', 'rdvAvenir', 'stats', 'specialites', 'notifications'));
    }

    public function index(Request $request)
    {
        $patient = $this->getPatient();
        $tab = $request->query('tab', 'a_venir');
        $today = Carbon::today();

        $query = RendezVous::where('patient_id', $patient->id)->with(['medecin.user', 'specialite']);

        if ($tab === 'a_venir') {
            $query->where('date_rdv', '>=', $today)->whereIn('statut', ['en_attente', 'confirme', 'arrive']);
        } elseif ($tab === 'historique') {
            $query->where(function ($q) use ($today) {
                $q->where('date_rdv', '<', $today)->orWhereIn('statut', ['termine', 'absent']);
            });
        } elseif ($tab === 'annules') {
            $query->where('statut', 'annule');
        }

        $rendezVous = $query->orderBy('date_rdv', 'desc')->orderBy('heure_rdv', 'desc')->paginate(10);

        return view('patient.rendez_vous.index', compact('rendezVous', 'tab'));
    }

    public function create(Request $request)
    {
        $specialites = Specialite::where('is_active', true)->with(['medecins' => function ($q) {
            $q->where('statut', 'actif')->with('user');
        }])->get();

        $selectedSpecialiteId = $request->query('specialite_id');
        $selectedMedecinId = $request->query('medecin_id');
        $selectedDate = $request->query('date', Carbon::tomorrow()->toDateString());

        $selectedMedecin = null;
        $slotsData = null;

        if ($selectedMedecinId) {
            $selectedMedecin = Medecin::with(['user', 'specialite', 'disponibilites'])->find($selectedMedecinId);
            if ($selectedMedecin) {
                $slotsData = $this->slotService->getSlotsForDoctorAndDate($selectedMedecin, $selectedDate);
            }
        }

        return view('patient.rendez_vous.create', compact(
            'specialites',
            'selectedSpecialiteId',
            'selectedMedecinId',
            'selectedMedecin',
            'selectedDate',
            'slotsData'
        ));
    }

    public function getSlots(Request $request)
    {
        $request->validate([
            'medecin_id' => 'required|exists:medecins,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $medecin = Medecin::findOrFail($request->medecin_id);
        $slots = $this->slotService->getSlotsForDoctorAndDate($medecin, $request->date);

        return response()->json($slots);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'medecin_id' => 'required|exists:medecins,id',
            'specialite_id' => 'required|exists:specialites,id',
            'date_rdv' => 'required|date|after_or_equal:today',
            'heure_rdv' => 'required|string',
            'motif' => 'nullable|string|max:255',
        ]);

        $patient = $this->getPatient();
        $medecin = Medecin::findOrFail($validated['medecin_id']);

        // Vérification de la disponibilité du créneau
        $slotsData = $this->slotService->getSlotsForDoctorAndDate($medecin, $validated['date_rdv']);
        $slotFound = false;

        foreach ($slotsData['slots'] as $slot) {
            if ($slot['heure'] === substr($validated['heure_rdv'], 0, 5) && $slot['statut'] === 'disponible') {
                $slotFound = true;
                break;
            }
        }

        if (!$slotFound) {
            return back()->withInput()->with('error', 'Désolé, ce créneau n\'est plus disponible. Veuillez en sélectionner un autre.');
        }

        $rdv = RendezVous::create([
            'patient_id' => $patient->id,
            'medecin_id' => $medecin->id,
            'specialite_id' => $validated['specialite_id'],
            'date_rdv' => $validated['date_rdv'],
            'heure_rdv' => $validated['heure_rdv'],
            'statut' => 'confirme',
            'motif' => $validated['motif'] ?? 'Consultation médicale',
        ]);

        // Nettoyer liste d'attente éventuelle
        ListeAttente::where('patient_id', $patient->id)
            ->where('medecin_id', $medecin->id)
            ->whereDate('date_souhaitee', $validated['date_rdv'])
            ->update(['statut' => 'converti']);

        // Traçabilité médico-légale
        \App\Services\AuditLogger::log(
            'CREATION_RDV',
            "Le patient {$patient->user->full_name} a réservé la consultation {$rdv->reference_rdv} avec Dr. {$medecin->nom_complet}",
            [
                'rendez_vous_id' => $rdv->id,
                'reference' => $rdv->reference_rdv,
                'patient_id' => $patient->id,
                'medecin_id' => $medecin->id,
            ]
        );

        // Notification pour le patient
        Notification::create([
            'user_id' => Auth::id(),
            'type' => 'confirmation',
            'titre' => 'Rendez-vous confirmé ' . $rdv->reference_rdv,
            'message' => 'Votre rendez-vous avec le ' . $medecin->nom_complet . ' est confirmé pour le ' . Carbon::parse($rdv->date_rdv)->format('d/m/Y') . ' à ' . substr($rdv->heure_rdv, 0, 5) . '.',
            'lien' => route('patient.rendez-vous.show', $rdv->id),
            'lu' => false,
        ]);

        // Notification pour le médecin traitant
        if ($medecin->user_id) {
            Notification::create([
                'user_id' => $medecin->user_id,
                'type' => 'confirmation',
                'titre' => 'Nouveau RDV : ' . $rdv->reference_rdv,
                'message' => 'Consultation réservée par ' . Auth::user()->full_name . ' le ' . Carbon::parse($rdv->date_rdv)->format('d/m/Y') . ' à ' . substr($rdv->heure_rdv, 0, 5) . '.',
                'lien' => route('medecin.planning', ['date' => $rdv->date_rdv]),
                'lu' => false,
            ]);
        }

        // Envoi de l'e-mail officiel de confirmation
        app(ReminderService::class)->sendConfirmation($rdv);

        return redirect()->route('patient.rendez-vous.show', $rdv->id)->with('success', 'Votre rendez-vous ' . $rdv->reference_rdv . ' a été réservé avec succès ! Une confirmation vous a été transmise.');
    }

    public function show(RendezVous $rendezVous)
    {
        $patient = $this->getPatient();
        if ($rendezVous->patient_id !== $patient->id && !Auth::user()->isAdmin() && !Auth::user()->isSecretaire()) {
            abort(403);
        }

        $rendezVous->load(['medecin.user', 'specialite', 'patient.user']);

        $patientUser = $rendezVous->patient ? $rendezVous->patient->user : null;
        $whatsappMessage = \App\Services\CommunicationHelper::formatWhatsAppMessage($rendezVous);
        $whatsappUrl = \App\Services\CommunicationHelper::generateWhatsAppUrl($patientUser?->telephone, $whatsappMessage);

        return view('patient.rendez_vous.show', compact('rendezVous', 'whatsappUrl', 'whatsappMessage'));
    }

    public function cancel(Request $request, RendezVous $rendezVous)
    {
        $patient = $this->getPatient();
        if ($rendezVous->patient_id !== $patient->id && !Auth::user()->isAdmin() && !Auth::user()->isSecretaire()) {
            abort(403);
        }

        $validated = $request->validate([
            'notes_annulation' => 'nullable|string|max:500',
        ]);

        $rendezVous->update([
            'statut' => 'annule',
            'annule_par' => 'patient',
            'notes_annulation' => $validated['notes_annulation'] ?? 'Annulé par le patient.',
        ]);

        // Traçabilité médico-légale
        \App\Services\AuditLogger::log(
            'ANNULATION_RDV',
            \Illuminate\Support\Facades\Auth::user()->full_name . " a annulé le rendez-vous {$rendezVous->reference_rdv}",
            [
                'rendez_vous_id' => $rendezVous->id,
                'reference' => $rendezVous->reference_rdv,
                'annule_par' => 'patient',
                'motif_annulation' => $validated['notes_annulation'] ?? null,
            ]
        );


        // Déclencher la notification pour les personnes en liste d'attente
        $this->slotService->handleAppointmentCancellation($rendezVous);

        // Notification confirmation annulation patient
        Notification::create([
            'user_id' => Auth::id(),
            'type' => 'annulation',
            'titre' => 'Rendez-vous annulé ' . $rendezVous->reference_rdv,
            'message' => 'Votre rendez-vous du ' . Carbon::parse($rendezVous->date_rdv)->format('d/m/Y') . ' a bien été annulé.',
            'lien' => route('patient.rendez-vous.show', $rendezVous->id),
            'lu' => false,
        ]);

        // Notification pour le médecin
        if ($rendezVous->medecin && $rendezVous->medecin->user_id) {
            Notification::create([
                'user_id' => $rendezVous->medecin->user_id,
                'type' => 'annulation',
                'titre' => 'Annulation RDV : ' . $rendezVous->reference_rdv,
                'message' => 'Le patient ' . Auth::user()->full_name . ' a annulé sa consultation du ' . Carbon::parse($rendezVous->date_rdv)->format('d/m/Y') . ' à ' . substr($rendezVous->heure_rdv, 0, 5) . '.',
                'lien' => route('medecin.planning', ['date' => $rendezVous->date_rdv]),
                'lu' => false,
            ]);
        }

        return redirect()->route('patient.rendez-vous.index')->with('success', 'Le rendez-vous a été annulé avec succès.');
    }

    public function joinWaitingList(Request $request)
    {
        $validated = $request->validate([
            'medecin_id' => 'required|exists:medecins,id',
            'specialite_id' => 'required|exists:specialites,id',
            'date_souhaitee' => 'required|date|after_or_equal:today',
            'plage_horaire' => 'nullable|string',
        ]);

        $patient = $this->getPatient();

        $alreadyInList = ListeAttente::where('patient_id', $patient->id)
            ->where('medecin_id', $validated['medecin_id'])
            ->whereDate('date_souhaitee', $validated['date_souhaitee'])
            ->where('statut', 'en_attente')
            ->first();

        if ($alreadyInList) {
            return back()->with('info', 'Vous êtes déjà inscrit sur la liste d\'attente pour cette date.');
        }

        ListeAttente::create([
            'patient_id' => $patient->id,
            'medecin_id' => $validated['medecin_id'],
            'specialite_id' => $validated['specialite_id'],
            'date_souhaitee' => $validated['date_souhaitee'],
            'plage_horaire' => $validated['plage_horaire'] ?? 'toute_la_journee',
            'statut' => 'en_attente',
        ]);

        Notification::create([
            'user_id' => Auth::id(),
            'type' => 'liste_attente',
            'titre' => 'Inscription sur liste d\'attente validée',
            'message' => 'Nous vous alerterons automatiquement dès qu\'un créneau se libèrera pour le ' . Carbon::parse($validated['date_souhaitee'])->format('d/m/Y') . '.',
            'lu' => false,
        ]);

        return back()->with('success', 'Inscription enregistrée ! Vous recevrez une notification prioritaire dès qu\'un créneau se libérera.');
    }

    /**
     * Générer l'attestation / convocation officielle imprimable et téléchargeable en PDF.
     */
    public function attestation(RendezVous $rendezVous)
    {
        $patient = $this->getPatient();
        if ($rendezVous->patient_id !== $patient->id && !Auth::user()->isAdmin() && !Auth::user()->isSecretaire() && !Auth::user()->isMedecin()) {
            abort(403);
        }

        $rendezVous->load(['medecin.user', 'specialite', 'patient.user']);

        return view('patient.rendez_vous.attestation', compact('rendezVous'));
    }

    /**
     * Exporter et imprimer l'historique complet des consultations du patient.
     */
    public function exportHistorique()
    {
        $patient = $this->getPatient();
        $rendezVous = RendezVous::where('patient_id', $patient->id)
            ->with(['medecin.user', 'specialite'])
            ->orderBy('date_rdv', 'desc')
            ->orderBy('heure_rdv', 'desc')
            ->get();

        return view('patient.historique_export', compact('patient', 'rendezVous'));
    }

    /**
     * Déclencher un rappel de consultation par SMS et Email (Simulation certifiée).
     */
    public function sendReminder(Request $request, RendezVous $rendezVous, ReminderService $reminderService)
    {
        $patient = $this->getPatient();
        if ($rendezVous->patient_id !== $patient->id && !Auth::user()->isAdmin() && !Auth::user()->isSecretaire() && !Auth::user()->isMedecin()) {
            abort(403);
        }

        $result = $reminderService->sendReminder($rendezVous);

        if ($result['status'] === 'success') {
            return back()->with('success', 'Rappel envoyé avec succès au ' . $result['recipient_phone'] . ' et par email à ' . $result['recipient_email'] . ' !');
        }

        return back()->with('error', $result['message'] ?? 'Erreur lors de l\'envoi du rappel.');
    }
}
