<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redirectByRole(Auth::user());
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    public function quickLogin(Request $request)
    {
        if (!config('app.show_demo_login')) {
            abort(403, 'Accès démo désactivé en production.');
        }

        $role = $request->input('role');
        $user = match ($role) {
            'admin' => User::where('role', 'admin')->first(),
            'secretaire' => User::where('role', 'secretaire')->first(),
            'medecin' => User::where('role', 'medecin')->first(),
            'patient' => User::where('role', 'patient')->first(),
            default => null,
        };

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();
            return $this->redirectByRole($user)->with('success', 'Connecté en tant que ' . $user->full_name . ' (' . ucfirst($user->role) . ')');
        }

        return redirect()->route('login')->with('error', 'Compte de démonstration introuvable.');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users'],
            'telephone' => ['required', 'string', 'max:50'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'sexe' => ['nullable', 'in:M,F,Autre'],
            'contact_urgence' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'],
            'role' => 'patient',
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        Patient::create([
            'user_id' => $user->id,
            'date_naissance' => $validated['date_naissance'] ?? null,
            'sexe' => $validated['sexe'] ?? null,
            'contact_urgence' => $validated['contact_urgence'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('patient.dashboard')->with('success', 'Bienvenue sur Hôpital RDV ! Votre compte a été créé avec succès.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Vous avez été déconnecté avec succès.');
    }

    public static function redirectByRole(User $user)
    {
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'secretaire' => redirect()->route('secretaire.guichet'),
            'medecin' => redirect()->route('medecin.dashboard'),
            'patient' => redirect()->route('patient.dashboard'),
            default => redirect('/'),
        };
    }
}
