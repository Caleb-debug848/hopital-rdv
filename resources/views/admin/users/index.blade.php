@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createUserModal: false, selectedRole: 'patient', editUserModal: false, editingUser: {} }">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight">Gestion des Comptes & Rôles</h1>
            <p class="text-xs text-slate-500">Administration centrale des accès : Administrateurs, Secrétariat, Médecins et Patients</p>
        </div>
        <button type="button" @click="createUserModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
            <i data-lucide="user-plus" class="w-4 h-4 text-brand-400"></i>
            Créer un Utilisateur
        </button>
    </div>

    <!-- Compteurs par Rôle -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <a href="{{ route('admin.users.index') }}" class="p-4 rounded-2xl border transition {{ !$role ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="text-[10px] font-bold uppercase tracking-wider {{ !$role ? 'text-slate-400' : 'text-slate-400' }}">Total Comptes</div>
            <div class="text-2xl font-heading font-extrabold mt-0.5 {{ !$role ? 'text-white' : 'text-slate-900' }}">{{ $statsRoles['total'] }}</div>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="p-4 rounded-2xl border transition {{ $role === 'admin' ? 'bg-purple-50 border-purple-300 ring-2 ring-purple-400' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="text-[10px] font-bold text-purple-700 uppercase tracking-wider flex items-center gap-1">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                Direction
            </div>
            <div class="text-2xl font-heading font-extrabold text-purple-700 mt-0.5">{{ $statsRoles['admin'] }}</div>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'secretaire']) }}" class="p-4 rounded-2xl border transition {{ $role === 'secretaire' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-400' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="text-[10px] font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1">
                <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i>
                Secrétariat
            </div>
            <div class="text-2xl font-heading font-extrabold text-amber-800 mt-0.5">{{ $statsRoles['secretaire'] }}</div>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'medecin']) }}" class="p-4 rounded-2xl border transition {{ $role === 'medecin' ? 'bg-sky-50 border-sky-300 ring-2 ring-sky-400' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="text-[10px] font-bold text-sky-700 uppercase tracking-wider flex items-center gap-1">
                <i data-lucide="stethoscope" class="w-3.5 h-3.5"></i>
                Médecins
            </div>
            <div class="text-2xl font-heading font-extrabold text-sky-700 mt-0.5">{{ $statsRoles['medecin'] }}</div>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'patient']) }}" class="p-4 rounded-2xl border transition {{ $role === 'patient' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider flex items-center gap-1">
                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                Patients
            </div>
            <div class="text-2xl font-heading font-extrabold text-emerald-700 mt-0.5">{{ $statsRoles['patient'] }}</div>
        </a>
    </div>

    <!-- Barre de Recherche -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex gap-2">
            @if($role)
                <input type="hidden" name="role" value="{{ $role }}">
            @endif
            <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher par nom, prénom, email ou téléphone..."
                   class="w-full px-4 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                Filtrer
            </button>
        </form>
    </div>

    <!-- Tableau des Utilisateurs -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-heading font-extrabold text-slate-900">
                Liste des Utilisateurs Enregistrés
            </h2>
            <div class="text-xs text-slate-400">
                Page {{ $users->currentPage() }} sur {{ $users->lastPage() }}
            </div>
        </div>

        @if($users->isEmpty())
            <div class="py-12 text-center text-xs text-slate-400">
                Aucun compte ne correspond à votre recherche.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">Utilisateur</th>
                            <th class="py-3 px-3">Email & Téléphone</th>
                            <th class="py-3 px-3">Rôle Assigné</th>
                            <th class="py-3 px-3">Détails Profil</th>
                            <th class="py-3 px-3">Date Création</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-3">
                                    <div class="font-bold text-slate-900">{{ $u->full_name }}</div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-600">
                                    <div>{{ $u->email }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $u->telephone }}</div>
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($u->role === 'admin')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            Direction
                                        </span>
                                    @elseif($u->role === 'medecin')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                            Médecin
                                        </span>
                                    @elseif($u->role === 'secretaire')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            Secrétariat
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Patient
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-slate-500">
                                    @if($u->patient)
                                        <span class="font-mono text-[11px]">Dossier : {{ $u->patient->numero_patient }}</span>
                                    @elseif($u->medecin)
                                        <span>{{ $u->medecin->specialite->nom ?? 'Praticien' }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-slate-400">
                                    {{ $u->created_at->format('d/m/Y') }}
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    @if($u->id !== Auth::id())
                                        <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression de ce compte ?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Supprimer" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Création Utilisateur -->
    <div x-show="createUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-lg w-full space-y-4 shadow-xl border border-slate-200 max-h-[90vh] overflow-y-auto" @click.outside="createUserModal = false">
            <div class="text-center space-y-1">
                <h3 class="text-base font-heading font-extrabold text-slate-900">Créer un Nouveau Compte</h3>
                <p class="text-xs text-slate-500">Ajout d'un nouvel utilisateur dans la base de données</p>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nom *</label>
                        <input type="text" name="nom" required placeholder="Nom" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Prénom *</label>
                        <input type="text" name="prenom" required placeholder="Prénom" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Email *</label>
                        <input type="email" name="email" required placeholder="email@exemple.com" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Téléphone *</label>
                        <input type="tel" name="telephone" required placeholder="+225 07..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Rôle *</label>
                    <select name="role" x-model="selectedRole" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                        <option value="patient">Patient</option>
                        <option value="medecin">Médecin</option>
                        <option value="secretaire">Secrétaire / Guichet</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>

                <div x-show="selectedRole === 'medecin'" class="space-y-3 p-3 bg-slate-50 rounded-xl border border-slate-200" x-cloak>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Spécialité Médicale *</label>
                        <select name="specialite_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            @foreach($specialites as $spe)
                                <option value="{{ $spe->id }}">{{ $spe->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Mot de passe temporaire *</label>
                    <input type="password" name="password" required placeholder="Minimum 6 caractères" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>

                <div class="flex items-center gap-3 pt-3">
                    <button type="button" @click="createUserModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                        Annuler
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                        Créer le Compte
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
