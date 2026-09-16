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
            'email' => 'required|email|max:191|unique:users',
            'telephone' => 'required|string|max:50',
            'specialite_id' => 'required|exists:specialites,id',
            'service' => 'nullable|string|max:100',
            'bureau' => 'nullable|string|max:50',
            'biographie' => 'nullable|string',
            'jours_consultation' => 'required|array|min:1',
            'heure_debut' => 'required|string',
            'heure_fin' => 'required|string',
            'password' => 'required|min:6',
        ]);

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
            'service' => $validated['service'] ?? null,
            'bureau' => $validated['bureau'] ?? null,
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

        return back()->with('success', 'Médecin ' . $medecin->nom_complet . ' créé avec succès !');
    }

    public function toggleMedecinStatut(Medecin $medecin)
    {
        $nouveauStatut = $medecin->statut === 'actif' ? 'inactif' : 'actif';
        $medecin->update(['statut' => $nouveauStatut]);

        return back()->with('success', 'Statut du médecin mis à jour : ' . $nouveauStatut);
    }

    public function specialites()
    {
        $specialites = Specialite::withCount('medecins')->withCount('rendezVous')->get();
        return view('admin.specialites.index', compact('specialites'));
    }

    public function storeSpecialite(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100|unique:specialites',
            'description' => 'nullable|string',
            'icone' => 'nullable|string|max:50',
        ]);

        Specialite::create([
            'nom' => $validated['nom'],
            'slug' => Str::slug($validated['nom']),
            'description' => $validated['description'] ?? null,
            'icone' => $validated['icone'] ?? 'stethoscope',
            'is_active' => true,
        ]);

        return back()->with('success', 'Spécialité ajoutée avec succès !');
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
