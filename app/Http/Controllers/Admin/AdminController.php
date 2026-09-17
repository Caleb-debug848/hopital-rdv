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

    public function statistiques(Request $request)
    {
        $periode = $request->query('periode', 'ce_mois');
        $dateDebut = null;
        $dateFin = Carbon::today()->endOfDay();

        switch ($periode) {
            case 'aujourdhui':
                $dateDebut = Carbon::today()->startOfDay();
                break;
            case 'cette_semaine':
                $dateDebut = Carbon::today()->startOfWeek();
                break;
            case 'ce_mois':
                $dateDebut = Carbon::today()->startOfMonth();
                break;
            case 'cette_annee':
                $dateDebut = Carbon::today()->startOfYear();
                break;
            case 'personnalise':
                $dateDebut = $request->query('date_debut') ? Carbon::parse($request->query('date_debut'))->startOfDay() : Carbon::today()->subMonth();
                $dateFin = $request->query('date_fin') ? Carbon::parse($request->query('date_fin'))->endOfDay() : Carbon::today()->endOfDay();
                break;
            default:
                $dateDebut = Carbon::today()->startOfMonth();
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

        // Rendez-vous par spécialité
        $bySpecialite = Specialite::withCount(['rendezVous' => function ($q) use ($dateDebut, $dateFin) {
            $q->whereBetween('date_rdv', [$dateDebut, $dateFin]);
        }])->orderBy('rendez_vous_count', 'desc')->get();

        // Rendez-vous par médecin
        $byMedecin = Medecin::with(['user', 'specialite'])->withCount(['rendezVous' => function ($q) use ($dateDebut, $dateFin) {
            $q->whereBetween('date_rdv', [$dateDebut, $dateFin]);
        }])->orderBy('rendez_vous_count', 'desc')->get();

        return view('admin.statistiques', compact(
            'periode',
            'dateDebut',
            'dateFin',
            'totalRdv',
            'statutsBreakdown',
            'bySpecialite',
            'byMedecin'
        ));
    }

    public function medecins()
    {
        $medecins = Medecin::with(['user', 'specialite', 'disponibilites'])->withCount('rendezVous')->get();
        $specialites = Specialite::where('is_active', true)->get();

        return view('admin.medecins.index', compact('medecins', 'specialites'));
    }

    public function storeMedecin(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|max:191|unique:users,email',
            'telephone' => 'required|string|max:50',
            'specialite_id' => 'required|exists:specialites,id',
            'service' => 'nullable|string|max:100',
            'bureau' => 'nullable|string|max:50',
            'biographie' => 'nullable|string',
            'jours_consultation' => 'required|array|min:1',
            'heure_debut' => 'required|string',
            'heure_fin' => 'required|string',
            'password' => 'required|min:6',
        ], [
            'email.unique' => 'Cette adresse email est déjà utilisée par un autre compte.',
            'password.required' => 'Le mot de passe de connexion est obligatoire (minimum 6 caractères).',
            'jours_consultation.required' => 'Veuillez cocher au moins un jour de consultation pour le praticien.',
            'specialite_id.required' => 'Veuillez sélectionner une spécialité médicale.',
        ]);

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
                'service' => $validated['service'] ?? 'Service Hospitalier',
                'bureau' => $validated['bureau'] ?? 'Bureau Consultations',
                'biographie' => $validated['biographie'] ?? null,
                'jours_consultation' => $validated['jours_consultation'],
                'heure_debut_defaut' => $validated['heure_debut'],
                'heure_fin_defaut' => $validated['heure_fin'],
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

            return back()->with('success', 'Médecin ' . $medecin->nom_complet . ' créé et activé avec succès !');
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
            'service' => 'nullable|string|max:100',
            'bureau' => 'nullable|string|max:50',
            'biographie' => 'nullable|string',
            'jours_consultation' => 'required|array|min:1',
            'heure_debut' => 'required|string',
            'heure_fin' => 'required|string',
            'password' => 'nullable|min:6',
        ]);

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
}
