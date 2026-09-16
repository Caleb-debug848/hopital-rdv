<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Disponibilite;
use App\Models\Medecin;
use App\Models\Patient;
use App\Models\Specialite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role');
        $search = $request->query('search');

        $query = User::with(['medecin.specialite', 'patient']);

        if ($role) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $specialites = Specialite::where('is_active', true)->get();

        $statsRoles = [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'secretaire' => User::where('role', 'secretaire')->count(),
            'medecin' => User::where('role', 'medecin')->count(),
            'patient' => User::where('role', 'patient')->count(),
        ];

        return view('admin.users.index', compact('users', 'role', 'search', 'specialites', 'statsRoles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191', 'unique:users'],
            'telephone' => ['required', 'string', 'max:50'],
            'role' => ['required', 'in:admin,secretaire,medecin,patient'],
            'password' => ['required', Password::min(6)],
            // Champs spécifiques médecin
            'specialite_id' => ['required_if:role,medecin', 'nullable', 'exists:specialites,id'],
            'service' => ['nullable', 'string', 'max:100'],
            'bureau' => ['nullable', 'string', 'max:50'],
            'jours_consultation' => ['nullable', 'array'],
            // Champs spécifiques patient
            'date_naissance' => ['nullable', 'date'],
            'sexe' => ['nullable', 'in:M,F,Autre'],
            'contact_urgence' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        if ($validated['role'] === 'patient') {
            Patient::create([
                'user_id' => $user->id,
                'date_naissance' => $validated['date_naissance'] ?? null,
                'sexe' => $validated['sexe'] ?? null,
                'contact_urgence' => $validated['contact_urgence'] ?? null,
            ]);
        } elseif ($validated['role'] === 'medecin') {
            $jours = $validated['jours_consultation'] ?? ['lundi', 'mercredi', 'vendredi'];
            $medecin = Medecin::create([
                'user_id' => $user->id,
                'specialite_id' => $validated['specialite_id'],
                'service' => $validated['service'] ?? 'Service Médical',
                'bureau' => $validated['bureau'] ?? 'Bureau Consultations',
                'jours_consultation' => $jours,
                'heure_debut_defaut' => '08:00:00',
                'heure_fin_defaut' => '16:00:00',
                'statut' => 'actif',
            ]);

            foreach ($jours as $jour) {
                Disponibilite::create([
                    'medecin_id' => $medecin->id,
                    'jour_semaine' => $jour,
                    'heure_debut' => '08:00:00',
                    'heure_fin' => '16:00:00',
                    'duree_creneau' => 30,
                    'is_active' => true,
                ]);
            }
        }

        return back()->with('success', 'Compte créé avec succès : ' . $user->full_name . ' (' . ucfirst($user->role) . ')');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email,' . $user->id],
            'telephone' => ['required', 'string', 'max:50'],
            'role' => ['required', 'in:admin,secretaire,medecin,patient'],
            'password' => ['nullable', Password::min(6)],
        ]);

        $user->nom = $validated['nom'];
        $user->prenom = $validated['prenom'];
        $user->email = $validated['email'];
        $user->telephone = $validated['telephone'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Compte mis à jour avec succès : ' . $user->full_name);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte administrateur.');
        }

        $userName = $user->full_name;
        $user->delete();

        return back()->with('success', 'Le compte de ' . $userName . ' a été supprimé.');
    }
}
