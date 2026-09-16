@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addSpecialiteModal: false }">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight">Spécialités Médicales</h1>
            <p class="text-xs text-slate-500">Organisation des pôles de consultations et services de santé</p>
        </div>
        <button type="button" @click="addSpecialiteModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
            <i data-lucide="plus" class="w-4 h-4 text-brand-400"></i>
            Ajouter une Spécialité
        </button>
    </div>

    <!-- Grille des Spécialités -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
        @foreach($specialites as $spe)
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-card hover:border-slate-300 transition flex flex-col justify-between space-y-4">
                <div class="space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm">
                        <i data-lucide="activity" class="w-5 h-5 text-slate-600"></i>
                    </div>
                    <h3 class="font-heading font-bold text-sm text-slate-900">{{ $spe->nom }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">{{ $spe->description ?? 'Service médical de consultation' }}</p>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-slate-500">
                    <span>{{ $spe->medecins_count }} médecin(s)</span>
                    <span class="text-brand-700 font-mono">{{ $spe->rendez_vous_count }} RDV</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Nouvelle Spécialité -->
    <div x-show="addSpecialiteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full space-y-4 shadow-xl border border-slate-200" @click.outside="addSpecialiteModal = false">
            <div class="text-center space-y-1">
                <h3 class="text-base font-heading font-extrabold text-slate-900">Nouvelle Spécialité Médicale</h3>
                <p class="text-xs text-slate-500">Créer un nouveau pôle de consultation pour l'hôpital</p>
            </div>

            <form action="{{ route('admin.specialites.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nom de la spécialité *</label>
                    <input type="text" name="nom" required placeholder="Ex: Rhumatologie, Oncologie..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description</label>
                    <textarea name="description" rows="3" placeholder="Présentation courte des actes et soins proposés..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="addSpecialiteModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                        Annuler
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
