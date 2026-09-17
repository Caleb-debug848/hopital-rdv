<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disponibilite;
use App\Models\Medecin;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Specialite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();

        $rdvAujourdhui = RendezVous::whereDate('date_rdv', $today)->get();

        $statsAujourdhui = [
            'total' => $rdvAujourdhui->count(),
            'confirmes' => $rdvAujourdhui->where('statut', 'confirme')->count(),
            'en_attente' => $rdvAujourdhui->where('statut', 'en_attente')->count(),
            'arrives' => $rdvAujourdhui->where('statut', 'arrive')->count(),
            'effectues' => $rdvAujourdhui->where('statut', 'termine')->count(),
            'annules' => $rdvAujourdhui->where('statut', 'annule')->count(),
            'absents' => $rdvAujourdhui->where('statut', 'absent')->count(),
        ];

        $globalStats = [
            'medecins' => Medecin::count(),
            'patients' => Patient::count(),
            'specialites' => Specialite::count(),
            'total_rdv' => RendezVous::count(),
        ];

        $derniersRdv = RendezVous::with(['patient.user', 'medecin.user', 'specialite'])
            ->latest()
            ->take(8)
            ->get();

        $specialitesStats = Specialite::withCount('rendezVous')
            ->orderBy('rendez_vous_count', 'desc')
            ->get();

        return view('admin.dashboard', compact('statsAujourdhui', 'globalStats', 'derniersRdv', 'specialitesStats'));
    }

    /**
     * Calcul centralisé des métriques de pilotage hospitalier et d'affluence
     */
    private function getStatistiquesData(string $periode = 'ce_mois', ?string $dateDebutCustom = null, ?string $dateFinCustom = null): array
    {
        $dateFin = Carbon::today()->endOfDay();

        switch ($periode) {
            case 'aujourdhui':
                $dateDebut = Carbon::today()->startOfDay();
                $periodeLabel = "Aujourd'hui (" . Carbon::today()->translatedFormat('d F Y') . ")";
                break;
            case 'cette_semaine':
                $dateDebut = Carbon::today()->startOfWeek();
                $periodeLabel = "Cette semaine (du " . Carbon::today()->startOfWeek()->format('d/m/Y') . " au " . Carbon::today()->endOfWeek()->format('d/m/Y') . ")";
                break;
            case 'ce_mois':
                $dateDebut = Carbon::today()->startOfMonth();
                $periodeLabel = "Mois de " . Carbon::today()->translatedFormat('F Y');
                break;
            case 'cette_annee':
                $dateDebut = Carbon::today()->startOfYear();
                $periodeLabel = "Année " . Carbon::today()->format('Y');
                break;
            case 'personnalise':
                $dateDebut = $dateDebutCustom ? Carbon::parse($dateDebutCustom)->startOfDay() : Carbon::today()->subMonth();
                $dateFin = $dateFinCustom ? Carbon::parse($dateFinCustom)->endOfDay() : Carbon::today()->endOfDay();
                $periodeLabel = "Période du " . $dateDebut->format('d/m/Y') . " au " . $dateFin->format('d/m/Y');
                break;
            default:
                $dateDebut = Carbon::today()->startOfMonth();
                $periodeLabel = "Mois de " . Carbon::today()->translatedFormat('F Y');
                break;
        }

        $query = RendezVous::whereBetween('date_rdv', [$dateDebut, $dateFin]);

        $totalRdv = (clone $query)->count();
        $statutsBreakdown = [
            'confirme' => (clone $query)->where('statut', 'confirme')->count(),
            'arrive' => (clone $query)->where('statut', 'arrive')->count(),
            'termine' => (clone $query)->where('statut', 'termine')->count(),
            'en_attente' => (clone $query)->where('statut', 'en_attente')->count(),
            'annule' => (clone $query)->where('statut', 'annule')->count(),
            'absent' => (clone $query)->where('statut', 'absent')->count(),
        ];

        // Indicateurs clés de performance médicale
        $tauxAbsenteisme = $totalRdv > 0 ? round(($statutsBreakdown['absent'] / $totalRdv) * 100, 1) : 0;
        $tauxPresence = $totalRdv > 0 ? round((($statutsBreakdown['termine'] + $statutsBreakdown['arrive']) / $totalRdv) * 100, 1) : 0;

        // Analyse de l'affluence et heures de pointe
        $creneauxHoraires = [
            '08h - 10h' => 0,
            '10h - 12h' => 0,
            '12h - 14h' => 0,
            '14h - 16h' => 0,
            '16h - 18h' => 0,
        ];

        $rdvsHoraires = (clone $query)->get(['id', 'heure_rdv']);
        foreach ($rdvsHoraires as $rdvItem) {
            $h = (int) substr($rdvItem->heure_rdv, 0, 2);
            if ($h >= 8 && $h < 10) $creneauxHoraires['08h - 10h']++;
            elseif ($h >= 10 && $h < 12) $creneauxHoraires['10h - 12h']++;
            elseif ($h >= 12 && $h < 14) $creneauxHoraires['12h - 14h']++;
            elseif ($h >= 14 && $h < 16) $creneauxHoraires['14h - 16h']++;
            elseif ($h >= 16 && $h < 18) $creneauxHoraires['16h - 18h']++;
        }

        // Rendez-vous par spécialité
        $bySpecialite = Specialite::withCount(['rendezVous' => function ($q) use ($dateDebut, $dateFin) {
            $q->whereBetween('date_rdv', [$dateDebut, $dateFin]);
        }])->orderBy('rendez_vous_count', 'desc')->get();

        // Rendez-vous par médecin avec cabinet
        $byMedecin = Medecin::with(['user', 'specialite', 'cabinet'])->withCount(['rendezVous' => function ($q) use ($dateDebut, $dateFin) {
            $q->whereBetween('date_rdv', [$dateDebut, $dateFin]);
        }])->orderBy('rendez_vous_count', 'desc')->get();

        // Tous les rendez-vous de la période avec leurs relations
        $rdvs = (clone $query)->with(['patient.user', 'medecin.user', 'specialite', 'medecin.cabinet'])
            ->orderBy('date_rdv', 'desc')
            ->orderBy('heure_rdv', 'desc')
            ->get();

        return compact(
            'periode',
            'periodeLabel',
            'dateDebut',
            'dateFin',
            'totalRdv',
            'statutsBreakdown',
            'tauxAbsenteisme',
            'tauxPresence',
            'creneauxHoraires',
            'bySpecialite',
            'byMedecin',
            'rdvs'
        );
    }

    public function statistiques(Request $request)
    {
        $periode = $request->query('periode', 'ce_mois');
        $data = $this->getStatistiquesData($periode, $request->query('date_debut'), $request->query('date_fin'));

        return view('admin.statistiques', $data);
    }

    /**
     * Rapport Exécutif A4 Haute Définition pour la Direction Médicale
     */
    public function rapportPdf(Request $request)
    {
        $periode = $request->query('periode', 'ce_mois');
        $data = $this->getStatistiquesData($periode, $request->query('date_debut'), $request->query('date_fin'));
        $parametres = \App\Models\Parametre::getSettings();

        return view('admin.rapport_export', array_merge($data, compact('parametres')));
    }

    /**
     * Export professionnel (Excel stylé .XLS ou CSV brut conforme) pour la Direction
     */
    public function exportStatistiques(Request $request)
    {
        $periode = $request->query('periode', 'ce_mois');
        $format = $request->query('format', 'excel');
        $data = $this->getStatistiquesData($periode, $request->query('date_debut'), $request->query('date_fin'));
        $parametres = \App\Models\Parametre::getSettings();

        // Export CSV brut (compatibilité tableurs et intégrations comptables)
        if ($format === 'csv') {
            $filename = 'rapport-activite-hospitaliere-' . now()->format('Ymd-His') . '.csv';

            $callback = function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

                fputcsv($handle, [
                    'Référence RDV',
                    'Date RDV',
                    'Heure RDV',
                    'Nom Patient',
                    'Téléphone Patient',
                    'Email Patient',
                    'Médecin Praticien',
                    'Spécialité',
                    'Bureau / Salle',
                    'Statut Consultation',
                    'Motif Médical',
                    'Date de Réservation'
                ], ';');

                foreach ($data['rdvs'] as $rdv) {
                    fputcsv($handle, [
                        $rdv->reference_rdv,
                        $rdv->date_rdv->format('d/m/Y'),
                        substr($rdv->heure_rdv, 0, 5),
                        $rdv->patient?->user?->full_name ?? 'Inconnu',
                        $rdv->patient?->user?->telephone ?? '—',
                        $rdv->patient?->user?->email ?? '—',
                        $rdv->medecin ? 'Dr. ' . $rdv->medecin->nom_complet : 'Non affecté',
                        $rdv->specialite?->nom ?? '—',
                        $rdv->medecin?->cabinet ? $rdv->medecin->cabinet->nom_court : ($rdv->medecin?->bureau ?? 'Standard'),
                        $rdv->statut_badge['label'],
                        $rdv->motif ?? 'Non spécifié',
                        $rdv->created_at->format('d/m/Y H:i')
                    ], ';');
                }

                fclose($handle);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // Export Excel Haute Définition stylé (.XLS avec couleurs, en-tête institutionnel et KPI)
        $filename = 'bilan-activite-hospitaliere-' . now()->format('Ymd-His') . '.xls';

        return response()->view('admin.exports.statistiques_excel', array_merge($data, compact('parametres')), 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Alias de rétro-compatibilité pour exportStatistiquesCsv
     */
    public function exportStatistiquesCsv(Request $request)
    {
        $request->merge(['format' => 'csv']);
        return $this->exportStatistiques($request);
    }


    public function medecins()
    {
        $medecins = Medecin::with(['user', 'specialite', 'disponibilites', 'cabinet'])->withCount('rendezVous')->get();
        $specialites = Specialite::where('is_active', true)->get();
        $cabinets = \App\Models\Cabinet::where('is_actif', true)->orderBy('batiment')->orderBy('nom')->get();

        return view('admin.medecins.index', compact('medecins', 'specialites', 'cabinets'));
    }

    public function storeMedecin(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|max:191|unique:users,email',
            'telephone' => 'required|string|max:50',
            'specialite_id' => 'required|exists:specialites,id',
            'cabinet_id' => 'nullable|exists:cabinets,id',
            'service' => 'nullable|string|max:100',
            'bureau' => 'nullable|string|max:50',
            'biographie' => 'nullable|string',
            'jours_consultation' => 'required|array|min:1',
            'heure_debut' => 'required|string',
            'heure_fin' => 'required|string',
            'password' => 'required|min:6',
        ]);

        if (!empty($validated['cabinet_id'])) {
            $cab = \App\Models\Cabinet::find($validated['cabinet_id']);
            if ($cab) {
                $validated['bureau'] = $cab->nom . ' (' . $cab->batiment . ', ' . $cab->etage . ')';
                $validated['service'] = $cab->batiment;
            }
        }

        return DB::transaction(function () use ($validated) {
            $user = User::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'telephone' => $validated['telephone'],
                'role' => 'medecin',
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ]);

            $medecin = Medecin::create([
                'user_id' => $user->id,
                'specialite_id' => $validated['specialite_id'],
                'cabinet_id' => $validated['cabinet_id'] ?? null,
                'titre' => 'Dr.',
                'service' => $validated['service'] ?? 'Service Hospitalier',
                'bureau' => $validated['bureau'] ?? 'Bureau Consultations',
                'biographie' => $validated['biographie'] ?? null,
                'jours_consultation' => $validated['jours_consultation'],
                'heure_debut_defaut' => $validated['heure_debut'],
                'heure_fin_defaut' => $validated['heure_fin'],
                'duree_consultation' => 30,
                'statut' => 'actif',
            ]);

            foreach ($validated['jours_consultation'] as $jour) {
                Disponibilite::create([
                    'medecin_id' => $medecin->id,
                    'jour_semaine' => $jour,
                    'heure_debut' => $validated['heure_debut'],
                    'heure_fin' => $validated['heure_fin'],
                    'duree_creneau' => 30,
                    'is_active' => true,
                ]);
            }

            \App\Services\AuditLogger::log(
                'CREATION_PRATICIEN',
                "Création du compte praticien Dr. {$medecin->nom_complet}",
                ['medecin_id' => $medecin->id, 'specialite_id' => $validated['specialite_id']]
            );

            return back()->with('success', 'Praticien ' . $medecin->nom_complet . ' créé avec succès !');
        });
    }

    public function updateMedecin(Request $request, Medecin $medecin)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|max:191|unique:users,email,' . $medecin->user_id,
            'telephone' => 'required|string|max:50',
            'specialite_id' => 'required|exists:specialites,id',
            'cabinet_id' => 'nullable|exists:cabinets,id',
            'service' => 'nullable|string|max:100',
            'bureau' => 'nullable|string|max:50',
            'biographie' => 'nullable|string',
            'jours_consultation' => 'required|array|min:1',
            'heure_debut' => 'required|string',
            'heure_fin' => 'required|string',
            'password' => 'nullable|min:6',
        ]);

        if (!empty($validated['cabinet_id'])) {
            $cab = \App\Models\Cabinet::find($validated['cabinet_id']);
            if ($cab) {
                $validated['bureau'] = $cab->nom . ' (' . $cab->batiment . ', ' . $cab->etage . ')';
                $validated['service'] = $cab->batiment;
            }
        }

        return DB::transaction(function () use ($validated, $medecin) {
            $userData = [
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'telephone' => $validated['telephone'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $medecin->user->update($userData);

            $medecin->update([
                'specialite_id' => $validated['specialite_id'],
                'cabinet_id' => $validated['cabinet_id'] ?? $medecin->cabinet_id,
                'service' => $validated['service'] ?? 'Service Hospitalier',
                'bureau' => $validated['bureau'] ?? 'Bureau Consultations',
                'biographie' => $validated['biographie'] ?? null,
                'jours_consultation' => $validated['jours_consultation'],
                'heure_debut_defaut' => $validated['heure_debut'],
                'heure_fin_defaut' => $validated['heure_fin'],
            ]);

            // Mettre à jour les disponibilités
            Disponibilite::where('medecin_id', $medecin->id)->delete();
            foreach ($validated['jours_consultation'] as $jour) {
                Disponibilite::create([
                    'medecin_id' => $medecin->id,
                    'jour_semaine' => $jour,
                    'heure_debut' => $validated['heure_debut'],
                    'heure_fin' => $validated['heure_fin'],
                    'duree_creneau' => 30,
                    'is_active' => true,
                ]);
            }

            \App\Services\AuditLogger::log(
                'MODIFICATION_PRATICIEN',
                "Mise à jour du profil du Dr. {$medecin->nom_complet}",
                ['medecin_id' => $medecin->id]
            );

            return back()->with('success', 'Fiche du Médecin ' . $medecin->nom_complet . ' mise à jour avec succès !');
        });
    }

    public function toggleMedecinStatut(Medecin $medecin)
    {
        $nouveauStatut = $medecin->statut === 'actif' ? 'inactif' : 'actif';
        $medecin->update(['statut' => $nouveauStatut]);

        return back()->with('success', 'Statut du Dr. ' . $medecin->nom_complet . ' : ' . ucfirst($nouveauStatut));
    }

    public function destroyMedecin(Medecin $medecin)
    {
        return DB::transaction(function () use ($medecin) {
            $nom = $medecin->nom_complet;
            Disponibilite::where('medecin_id', $medecin->id)->delete();
            $user = $medecin->user;
            $medecin->delete();
            if ($user) {
                $user->delete();
            }

            return back()->with('success', 'Le médecin Dr. ' . $nom . ' a été supprimé.');
        });
    }

    public function specialites()
    {
        $specialites = Specialite::withCount('medecins')->withCount('rendezVous')->get();
        return view('admin.specialites.index', compact('specialites'));
    }

    public function storeSpecialite(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100|unique:specialites,nom',
            'description' => 'nullable|string',
            'icone' => 'nullable|string|max:50',
        ], [
            'nom.unique' => 'Cette spécialité existe déjà dans la base de données.',
            'nom.required' => 'Le nom de la spécialité est obligatoire.',
        ]);

        Specialite::create([
            'nom' => $validated['nom'],
            'slug' => Str::slug($validated['nom']),
            'description' => $validated['description'] ?? null,
            'icone' => $validated['icone'] ?? 'stethoscope',
            'is_active' => true,
        ]);

        return back()->with('success', 'Spécialité [' . $validated['nom'] . '] ajoutée avec succès !');
    }

    public function updateSpecialite(Request $request, Specialite $specialite)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100|unique:specialites,nom,' . $specialite->id,
            'description' => 'nullable|string',
            'icone' => 'nullable|string|max:50',
        ]);

        $specialite->update([
            'nom' => $validated['nom'],
            'slug' => Str::slug($validated['nom']),
            'description' => $validated['description'] ?? null,
            'icone' => $validated['icone'] ?? 'stethoscope',
        ]);

        return back()->with('success', 'Spécialité [' . $specialite->nom . '] mise à jour avec succès !');
    }

    public function destroySpecialite(Specialite $specialite)
    {
        if ($specialite->medecins()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer cette spécialité car des médecins y sont affectés. Réaffectez d\'abord les médecins.');
        }

        $nom = $specialite->nom;
        $specialite->delete();

        return back()->with('success', 'Spécialité [' . $nom . '] supprimée avec succès.');
    }

    public function patients(Request $request)
    {
        $search = $request->query('search');
        $query = Patient::with(['user'])->withCount('rendezVous');

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%");
            })->orWhere('numero_patient', 'like', "%{$search}%");
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.patients.index', compact('patients', 'search'));
    }

    /**
     * Dossier Patient 360° Unique et Centralisé
     */
    public function patientShow(Patient $patient)
    {
        $patient->load(['user', 'rendezVous.medecin.user', 'rendezVous.specialite']);

        $totalRdv = $patient->rendezVous->count();
        $effectues = $patient->rendezVous->where('statut', 'termine')->count();
        $aVenir = $patient->rendezVous->whereIn('statut', ['en_attente', 'confirme', 'arrive'])->count();
        $annules = $patient->rendezVous->where('statut', 'annule')->count();
        $absents = $patient->rendezVous->where('statut', 'absent')->count();

        $tauxAssiduite = $totalRdv > 0 ? round((($effectues + $aVenir) / $totalRdv) * 100) : 100;

        $medecinsConsultes = $patient->rendezVous
            ->pluck('medecin')
            ->filter()
            ->unique('id')
            ->values();

        return view('admin.patients.show', compact(
            'patient',
            'totalRdv',
            'effectues',
            'aVenir',
            'annules',
            'absents',
            'tauxAssiduite',
            'medecinsConsultes'
        ));
    }

    /**
     * Accès administratif aux attestations certifiées
     */
    public function attestation(RendezVous $rendezVous)
    {
        $rendezVous->load(['medecin.user', 'specialite', 'patient.user']);
        return view('patient.rendez_vous.attestation', compact('rendezVous'));
    }

    /**
     * Journal d'Audit & Traçabilité Médico-Légale
     */
    public function auditLogs(Request $request)
    {
        $search = $request->query('search');
        $action = $request->query('action');
        $role = $request->query('role');
        $date = $request->query('date');

        $query = \App\Models\AuditLog::with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($action) {
            $query->where('action', $action);
        }

        if ($role) {
            $query->where('user_role', $role);
        }

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        $actionsList = \App\Models\AuditLog::select('action')->distinct()->pluck('action');

        $totalAujourdhui = \App\Models\AuditLog::whereDate('created_at', Carbon::today())->count();
        $totalPointages = \App\Models\AuditLog::where('action', 'POINTAGE_ARRIVEE')->count();
        $totalAnnulations = \App\Models\AuditLog::where('action', 'ANNULATION_RDV')->count();

        return view('admin.audit_logs.index', compact(
            'logs',
            'search',
            'action',
            'role',
            'date',
            'actionsList',
            'totalAujourdhui',
            'totalPointages',
            'totalAnnulations'
        ));
    }

    /**
     * Paramètres Généraux de l'Établissement Hospitalier
     */
    public function parametres()
    {
        $parametres = \App\Models\Parametre::getSettings();
        return view('admin.parametres', compact('parametres'));
    }

    public function updateParametres(Request $request)
    {
        $validated = $request->validate([
            'nom_hopital' => 'required|string|max:191',
            'slogan' => 'nullable|string|max:255',
            'adresse' => 'required|string|max:255',
            'telephone' => 'required|string|max:50',
            'email_contact' => 'required|email|max:191',
            'duree_creneau_defaut' => 'required|integer|in:15,20,30,45,60',
            'heure_ouverture' => 'required|string',
            'heure_fermeture' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
        ]);

        $parametres = \App\Models\Parametre::getSettings();

        $updateData = [
            'nom_hopital' => $validated['nom_hopital'],
            'slogan' => $validated['slogan'] ?? null,
            'adresse' => $validated['adresse'],
            'telephone' => $validated['telephone'],
            'email_contact' => $validated['email_contact'],
            'duree_creneau_defaut' => $validated['duree_creneau_defaut'],
            'heure_ouverture' => $validated['heure_ouverture'],
            'heure_fermeture' => $validated['heure_fermeture'],
        ];

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('hospital', 'public');
            $updateData['logo_path'] = $path;
        }

        $parametres->update($updateData);
        \App\Models\Parametre::clearCache();

        // Traçabilité dans le journal d'audit
        \App\Services\AuditLogger::log(
            'PARAMETRES_MODIFIES',
            \Illuminate\Support\Facades\Auth::user()->full_name . " a mis à jour les paramètres généraux de l'établissement",
            $updateData
        );

        return back()->with('success', 'Paramètres de l\'établissement hospitalier enregistrés avec succès !');
    }

    /**
     * Gestion des Services & Cabinets Médicaux
     */
    public function cabinets()
    {
        $cabinets = \App\Models\Cabinet::with(['medecins.user', 'medecins.specialite'])
            ->orderBy('batiment')
            ->orderBy('nom')
            ->get();

        $batiments = $cabinets->pluck('batiment')->unique()->values();
        $capaciteTotale = $cabinets->sum('capacite');

        return view('admin.cabinets.index', compact('cabinets', 'batiments', 'capaciteTotale'));
    }

    public function storeCabinet(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'batiment' => 'required|string|max:100',
            'etage' => 'required|string|max:50',
            'capacite' => 'required|integer|min:1|max:50',
            'equipements' => 'nullable|string|max:255',
        ]);

        $cabinet = \App\Models\Cabinet::create([
            'nom' => $validated['nom'],
            'batiment' => $validated['batiment'],
            'etage' => $validated['etage'],
            'capacite' => $validated['capacite'],
            'equipements' => $validated['equipements'] ?? null,
            'is_actif' => true,
        ]);

        \App\Services\AuditLogger::log(
            'CREATION_CABINET',
            \Illuminate\Support\Facades\Auth::user()->full_name . " a créé le cabinet {$cabinet->nom} ({$cabinet->batiment})",
            $validated
        );

        return back()->with('success', 'Cabinet / Salle ' . $cabinet->nom . ' ajouté avec succès !');
    }

    public function updateCabinet(Request $request, \App\Models\Cabinet $cabinet)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'batiment' => 'required|string|max:100',
            'etage' => 'required|string|max:50',
            'capacite' => 'required|integer|min:1|max:50',
            'equipements' => 'nullable|string|max:255',
            'is_actif' => 'required|boolean',
        ]);

        $cabinet->update($validated);

        \App\Services\AuditLogger::log(
            'MODIFICATION_CABINET',
            \Illuminate\Support\Facades\Auth::user()->full_name . " a modifié le cabinet {$cabinet->nom}",
            $validated
        );

        return back()->with('success', 'Cabinet ' . $cabinet->nom . ' mis à jour avec succès !');
    }

    public function destroyCabinet(\App\Models\Cabinet $cabinet)
    {
        if ($cabinet->medecins()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer cette salle car des médecins y sont affectés. Veuillez d\'abord modifier leur affectation.');
        }

        $nom = $cabinet->nom;
        $cabinet->delete();

        \App\Services\AuditLogger::log(
            'SUPPRESSION_CABINET',
            \Illuminate\Support\Facades\Auth::user()->full_name . " a supprimé le cabinet {$nom}"
        );

        return back()->with('success', 'Cabinet ' . $nom . ' supprimé avec succès.');
    }

    /**
     * Suivi des Rappels & Supervision des Notifications
     */
    public function rappels(Request $request)
    {
        $search = $request->query('search');
        $type = $request->query('type');

        $query = \App\Models\Notification::with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('titre', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('telephone', 'like', "%{$search}%");
                    });
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        $rappelsAujourdhui = \App\Models\Notification::whereDate('created_at', Carbon::today())
            ->whereIn('type', ['rappel', 'rappel_automatique'])
            ->count();

        $rappelsCeMois = \App\Models\Notification::where('created_at', '>=', Carbon::today()->startOfMonth())
            ->whereIn('type', ['rappel', 'rappel_automatique'])
            ->count();

        $totalNotifications = \App\Models\Notification::count();

        // RDV prévus demain qui recevront le prochain rappel à 08h00
        $rdvsDemain = RendezVous::whereDate('date_rdv', Carbon::tomorrow())
            ->whereIn('statut', ['confirme', 'en_attente'])
            ->with(['patient.user', 'medecin.user', 'specialite'])
            ->get();

        return view('admin.rappels.index', compact(
            'notifications',
            'search',
            'type',
            'rappelsAujourdhui',
            'rappelsCeMois',
            'totalNotifications',
            'rdvsDemain'
        ));
    }

    public function triggerRappelsNow()
    {
        \Illuminate\Support\Facades\Artisan::call('rdv:send-reminders');
        $output = \Illuminate\Support\Facades\Artisan::output();

        \App\Services\AuditLogger::log(
            'DECLENCHEMENT_RAPPELS',
            \Illuminate\Support\Facades\Auth::user()->full_name . " a déclenché manuellement l'envoi des rappels de consultation",
            ['output' => $output]
        );

        return back()->with('success', 'Envoi des rappels exécuté avec succès ! ' . trim($output));
    }
}





