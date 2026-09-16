@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addDoctorModal: false }">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight">Corps Médical & Praticiens</h1>
            <p class="text-xs text-slate-500">Gestion des comptes médecins, plannings de consultation et affectations</p>
        </div>
        <button type="button" @click="addDoctorModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
            <i data-lucide="user-plus" class="w-4 h-4 text-brand-400"></i>
            Ajouter un Praticien
        </button>
    </div>

    <!-- Grille des Médecins -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($medecins as $med)
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-card hover:border-slate-300 transition space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="w-11 h-11 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm">
                            <i data-lucide="stethoscope" class="w-5 h-5 text-slate-600"></i>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $med->statut === 'actif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                            {{ ucfirst($med->statut) }}
                        </span>
                    </div>

                    <div>
                        <h3 class="font-heading font-bold text-sm text-slate-900">{{ $med->nom_complet }}</h3>
                        <p class="text-xs font-semibold text-brand-600">{{ $med->specialite->nom }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $med->service ?? 'Service Hospitalier' }} • {{ $med->bureau ?? 'Bureau' }}</p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1 text-slate-600">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span>{{ $med->user->telephone }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span class="truncate">{{ $med->user->email }}</span>
                        </div>
                        <div class="text-[11px] text-slate-500 pt-1 flex items-center gap-1">
                            <i data-lucide="calendar" class="w-3 h-3 text-slate-400"></i>
                            <span>Jours : <strong class="text-slate-700">{{ is_array($med->jours_consultation) ? implode(', ', $med->jours_consultation) : 'Sur RDV' }}</strong></span>
                        </div>
                        <div class="text-[11px] text-slate-500 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3 text-slate-400"></i>
                            <span>Horaires : {{ substr($med->heure_debut_defaut, 0, 5) }} - {{ substr($med->heure_fin_defaut, 0, 5) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs">
                    <span class="font-bold text-slate-700">{{ $med->rendez_vous_count }} consultations</span>
                    <form action="{{ route('admin.medecins.toggle', $med->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="text-[11px] font-bold {{ $med->statut === 'actif' ? 'text-rose-600 hover:underline' : 'text-emerald-600 hover:underline' }}">
                            {{ $med->statut === 'actif' ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Création Médecin -->
    <div x-show="addDoctorModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-lg w-full space-y-4 shadow-xl border border-slate-200 overflow-y-auto max-h-[90vh]" @click.outside="addDoctorModal = false">
            <div class="text-center space-y-1">
                <h3 class="text-base font-heading font-extrabold text-slate-900">Enregistrer un Nouveau Médecin</h3>
                <p class="text-xs text-slate-500">Création du profil praticien et affectation de son emploi du temps</p>
            </div>

            <form action="{{ route('admin.medecins.store') }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nom *</label>
                        <input type="text" name="nom" required placeholder="Ex: Konan" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Prénom *</label>
                        <input type="text" name="prenom" required placeholder="Ex: Jean" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Email de connexion *</label>
                        <input type="email" name="email" required placeholder="dr.nom@hopital.com" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Téléphone *</label>
                        <input type="tel" name="telephone" required placeholder="+225 07..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Spécialité *</label>
                        <select name="specialite_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            @foreach($specialites as $spe)
                                <option value="{{ $spe->id }}">{{ $spe->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Bureau / Cabinet</label>
                        <input type="text" name="bureau" placeholder="Ex: Bâtiment B, Porte 104" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Heure début *</label>
                        <input type="time" name="heure_debut" value="08:00" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Heure fin *</label>
                        <input type="time" name="heure_fin" value="16:00" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Jours de consultation</label>
                    <div class="flex flex-wrap gap-2 text-xs">
                        @foreach(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'] as $jour)
                            <label class="inline-flex items-center gap-1 bg-slate-50 px-2 py-1 rounded-lg border border-slate-200 cursor-pointer">
                                <input type="checkbox" name="jours_consultation[]" value="{{ $jour }}" checked class="rounded text-brand-600 focus:ring-brand-500">
                                <span class="capitalize text-[11px]">{{ substr($jour, 0, 3) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-3">
                    <button type="button" @click="addDoctorModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                        Annuler
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                        Créer le Médecin
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
