@extends('layouts.app')

@section('content')
<div class="space-y-6" 
     x-data="{ 
         createUserModal: {{ $errors->any() && !old('_method') ? 'true' : 'false' }}, 
         selectedRole: '{{ old('role', 'patient') }}', 
         editUserModal: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }}, 
         showCreatePass: false,
         showEditPass: false,
         editingUser: {
             id: '',
             nom: '',
             prenom: '',
             email: '',
             telephone: '',
             role: 'patient'
         },
         openEdit(u) {
             this.editingUser = { ...u };
             this.editUserModal = true;
         }
     }">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight">Gestion des Comptes & Rôles</h1>
            <p class="text-xs text-slate-500">Administration centrale des accès : Direction, Secrétariat, Médecins et Patients</p>
        </div>
        <button type="button" @click="createUserModal = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition min-h-[44px]">
            <i data-lucide="user-plus" class="w-4 h-4 text-brand-400"></i>
            Créer un Utilisateur
        </button>
    </div>

    <!-- Compteurs par Rôle -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3">
        <a href="{{ route('admin.users.index') }}" class="p-3.5 sm:p-4 rounded-2xl border transition {{ !$role ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="text-[10px] font-bold uppercase tracking-wider {{ !$role ? 'text-slate-400' : 'text-slate-400' }}">Total Comptes</div>
            <div class="text-xl sm:text-2xl font-heading font-extrabold mt-0.5 {{ !$role ? 'text-white' : 'text-slate-900' }}">{{ $statsRoles['total'] }}</div>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="p-3.5 sm:p-4 rounded-2xl border transition {{ $role === 'admin' ? 'bg-purple-50 border-purple-300 ring-2 ring-purple-400' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="text-[10px] font-bold text-purple-700 uppercase tracking-wider flex items-center gap-1">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                Direction
            </div>
            <div class="text-xl sm:text-2xl font-heading font-extrabold text-purple-700 mt-0.5">{{ $statsRoles['admin'] }}</div>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'secretaire']) }}" class="p-3.5 sm:p-4 rounded-2xl border transition {{ $role === 'secretaire' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-400' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="text-[10px] font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1">
                <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i>
                Secrétariat
            </div>
            <div class="text-xl sm:text-2xl font-heading font-extrabold text-amber-800 mt-0.5">{{ $statsRoles['secretaire'] }}</div>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'medecin']) }}" class="p-3.5 sm:p-4 rounded-2xl border transition {{ $role === 'medecin' ? 'bg-sky-50 border-sky-300 ring-2 ring-sky-400' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
            <div class="text-[10px] font-bold text-sky-700 uppercase tracking-wider flex items-center gap-1">
                <i data-lucide="stethoscope" class="w-3.5 h-3.5"></i>
                Médecins
            </div>
            <div class="text-xl sm:text-2xl font-heading font-extrabold text-sky-700 mt-0.5">{{ $statsRoles['medecin'] }}</div>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'patient']) }}" class="p-3.5 sm:p-4 rounded-2xl border transition {{ $role === 'patient' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400' : 'bg-white border-slate-200/80 hover:border-slate-300' }} col-span-2 sm:col-span-1">
            <div class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider flex items-center gap-1">
                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                Patients
            </div>
            <div class="text-xl sm:text-2xl font-heading font-extrabold text-emerald-700 mt-0.5">{{ $statsRoles['patient'] }}</div>
        </a>
    </div>

    <!-- Barre de Recherche -->
    <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-card">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2">
            @if($role)
                <input type="hidden" name="role" value="{{ $role }}">
            @endif
            <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher par nom, prénom, email ou téléphone..."
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium min-h-[44px]">
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs min-h-[44px] shrink-0">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                Rechercher
            </button>
        </form>
    </div>

    <!-- Tableau & Cartes des Utilisateurs -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden p-4 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
            <h2 class="text-base font-heading font-extrabold text-slate-900">
                Liste des Comptes
            </h2>
            <div class="text-xs text-slate-400 font-medium">
                Page {{ $users->currentPage() }} sur {{ $users->lastPage() }} ({{ $users->total() }} total)
            </div>
        </div>

        @if($users->isEmpty())
            <div class="py-12 text-center text-xs text-slate-400">
                Aucun compte ne correspond à votre recherche.
            </div>
        @else
            <!-- Vue Cartes Mobile (écrans < 640px) -->
            <div class="block sm:hidden space-y-3">
                @foreach($users as $u)
                    <div class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm leading-snug">{{ $u->full_name }}</h3>
                                <p class="text-[11px] text-slate-400">Inscrit le {{ $u->created_at->format('d/m/Y') }}</p>
                            </div>
                            @if($u->role === 'admin')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200 shrink-0">
                                    Direction
                                </span>
                            @elseif($u->role === 'medecin')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200 shrink-0">
                                    Médecin
                                </span>
                            @elseif($u->role === 'secretaire')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 shrink-0">
                                    Secrétariat
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                    Patient
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 gap-1.5 text-xs text-slate-600 bg-white p-2.5 rounded-lg border border-slate-100">
                            <a href="mailto:{{ $u->email }}" class="flex items-center gap-2 hover:text-brand-600 truncate">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                <span class="truncate">{{ $u->email }}</span>
                            </a>
                            <a href="tel:{{ $u->telephone }}" class="flex items-center gap-2 hover:text-brand-600 font-mono text-[11px]">
                                <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                <span>{{ $u->telephone }}</span>
                            </a>
                            @if($u->patient)
                                <div class="flex items-center gap-2 text-[11px] text-slate-500 pt-1 border-t border-slate-100">
                                    <i data-lucide="folder" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                    <span class="font-mono">Dossier : {{ $u->patient->numero_patient }}</span>
                                </div>
                            @elseif($u->medecin)
                                <div class="flex items-center gap-2 text-[11px] text-brand-600 font-semibold pt-1 border-t border-slate-100">
                                    <i data-lucide="stethoscope" class="w-3.5 h-3.5 text-brand-500 shrink-0"></i>
                                    <span>{{ $u->medecin->specialite->nom ?? 'Praticien' }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Actions tactiles -->
                        <div class="flex items-center gap-2 pt-1">
                            <button type="button" 
                                    @click="openEdit({
                                        id: {{ $u->id }},
                                        nom: '{{ addslashes($u->nom) }}',
                                        prenom: '{{ addslashes($u->prenom) }}',
                                        email: '{{ addslashes($u->email) }}',
                                        telephone: '{{ addslashes($u->telephone) }}',
                                        role: '{{ $u->role }}'
                                    })"
                                    class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-brand-600 min-h-[40px] transition">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                Modifier
                            </button>
                            @if($u->id !== Auth::id())
                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression du compte de {{ addslashes($u->full_name) }} ?')" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-lg bg-rose-50 border border-rose-200 text-xs font-bold text-rose-700 hover:bg-rose-100 min-h-[40px] transition">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        Supprimer
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Vue Tableau Desktop (écrans >= 640px) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">Utilisateur</th>
                            <th class="py-3 px-3">Email & Téléphone</th>
                            <th class="py-3 px-3">Rôle</th>
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
                                        <span class="font-mono text-[11px] bg-slate-100 px-2 py-0.5 rounded">Dossier : {{ $u->patient->numero_patient }}</span>
                                    @elseif($u->medecin)
                                        <span class="font-semibold text-brand-600">{{ $u->medecin->specialite->nom ?? 'Praticien' }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 text-slate-400">
                                    {{ $u->created_at->format('d/m/Y') }}
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Modifier -->
                                        <button type="button" 
                                                @click="openEdit({
                                                    id: {{ $u->id }},
                                                    nom: '{{ addslashes($u->nom) }}',
                                                    prenom: '{{ addslashes($u->prenom) }}',
                                                    email: '{{ addslashes($u->email) }}',
                                                    telephone: '{{ addslashes($u->telephone) }}',
                                                    role: '{{ $u->role }}'
                                                })"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition" title="Modifier">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        <!-- Supprimer -->
                                        @if($u->id !== Auth::id())
                                            <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression du compte de {{ addslashes($u->full_name) }} ?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Supprimer" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
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

    <!-- ============================================== -->
    <!-- MODAL 1 : CRÉATION D'UN UTILISATEUR            -->
    <!-- ============================================== -->
    <div x-show="createUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-lg w-full space-y-4 shadow-xl border border-slate-200 max-h-[90vh] overflow-y-auto" @click.outside="createUserModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-heading font-extrabold text-slate-900">Créer un Nouveau Compte</h3>
                    <p class="text-xs text-slate-500">Ajout d'un nouvel utilisateur dans la base de données</p>
                </div>
                <button type="button" @click="createUserModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nom *</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required placeholder="Ex: Kouame" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium min-h-[44px]">
                        @error('nom') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Prénom *</label>
                        <input type="text" name="prenom" value="{{ old('prenom') }}" required placeholder="Ex: Marie" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium min-h-[44px]">
                        @error('prenom') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Email de connexion *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@exemple.com" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium min-h-[44px]">
                        @error('email') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Téléphone *</label>
                        <input type="tel" name="telephone" value="{{ old('telephone') }}" required placeholder="+33 6..." class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium min-h-[44px]">
                        @error('telephone') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Rôle dans l'établissement *</label>
                    <select name="role" x-model="selectedRole" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-brand-500 font-medium min-h-[44px]">
                        <option value="patient">Patient</option>
                        <option value="medecin">Médecin</option>
                        <option value="secretaire">Secrétaire / Guichet</option>
                        <option value="admin">Direction / Administrateur</option>
                    </select>
                </div>

                <!-- Champs conditionnels pour Médecin -->
                <div x-show="selectedRole === 'medecin'" class="space-y-3 p-3 bg-slate-50 rounded-xl border border-slate-200" x-cloak>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Spécialité Médicale *</label>
                        <select name="specialite_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white font-medium min-h-[44px]">
                            @foreach($specialites as $spe)
                                <option value="{{ $spe->id }}">{{ $spe->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Mot de passe obligatoire à la création -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Mot de passe de connexion *</label>
                    <div class="relative">
                        <input :type="showCreatePass ? 'text' : 'password'" name="password" required placeholder="Minimum 6 caractères" class="w-full px-3 py-2.5 pr-10 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium min-h-[44px]">
                        <button type="button" @click="showCreatePass = !showCreatePass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1">
                            <i :data-lucide="showCreatePass ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                        </button>
                    </div>
                    @error('password') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="createUserModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition min-h-[44px]">
                        Annuler
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition flex items-center justify-center gap-2 min-h-[44px]">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Créer le Compte
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 2 : MODIFICATION D'UN UTILISATEUR        -->
    <!-- ============================================== -->
    <div x-show="editUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-lg w-full space-y-4 shadow-xl border border-slate-200 max-h-[90vh] overflow-y-auto" @click.outside="editUserModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-heading font-extrabold text-slate-900">Modifier l'Utilisateur</h3>
                    <p class="text-xs text-slate-500">Mise à jour des coordonnées et des droits d'accès</p>
                </div>
                <button type="button" @click="editUserModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/utilisateurs') }}/' + editingUser.id" method="POST" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nom *</label>
                        <input type="text" name="nom" x-model="editingUser.nom" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium min-h-[44px]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Prénom *</label>
                        <input type="text" name="prenom" x-model="editingUser.prenom" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium min-h-[44px]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Email *</label>
                        <input type="email" name="email" x-model="editingUser.email" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium min-h-[44px]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Téléphone *</label>
                        <input type="tel" name="telephone" x-model="editingUser.telephone" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium min-h-[44px]">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Rôle *</label>
                    <select name="role" x-model="editingUser.role" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs bg-white font-medium min-h-[44px]">
                        <option value="patient">Patient</option>
                        <option value="medecin">Médecin</option>
                        <option value="secretaire">Secrétaire / Guichet</option>
                        <option value="admin">Direction / Administrateur</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <div class="relative">
                        <input :type="showEditPass ? 'text' : 'password'" name="password" placeholder="Laisser vide pour conserver l'actuel" class="w-full px-3 py-2.5 pr-10 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium min-h-[44px]">
                        <button type="button" @click="showEditPass = !showEditPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1">
                            <i :data-lucide="showEditPass ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="editUserModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition min-h-[44px]">
                        Annuler
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-xs transition flex items-center justify-center gap-2 min-h-[44px]">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
