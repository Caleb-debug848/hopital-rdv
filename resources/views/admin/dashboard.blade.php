@extends('layouts.app')

@section('content')
<div class="space-y-8">

    <!-- En-tête Admin Professionnel -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center font-heading font-bold text-lg shadow-sm">
                <i data-lucide="shield" class="w-6 h-6 text-brand-400"></i>
            </div>
            <div>
                <h1 class="text-xl font-heading font-extrabold text-slate-900">Direction & Administration</h1>
                <p class="text-xs text-slate-500">Supervision hospitalière globale, indicateurs de performance et gestion des flux</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.statistiques') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition">
                <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                Rapports Détaillés
            </a>
        </div>
    </div>

    <!-- 1. Chiffres Clés du Jour (Conforme Cahier des Charges) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-heading font-extrabold uppercase tracking-wider text-slate-400">
                Aujourd'hui — {{ \Carbon\Carbon::today()->translatedFormat('l d F Y') }}
            </h2>
            <span class="text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
                {{ $statsAujourdhui['total'] }} Rendez-vous
            </span>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 sm:gap-3">
            <div class="bg-white p-2 sm:p-3.5 rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-card text-center">
                <div class="text-[9px] sm:text-[10px] font-bold text-emerald-600 uppercase tracking-tight">Confirmés</div>
                <div class="text-lg sm:text-2xl font-heading font-extrabold text-emerald-600 mt-0.5 sm:mt-1">{{ $statsAujourdhui['confirmes'] }}</div>
            </div>
            <div class="bg-white p-2 sm:p-3.5 rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-card text-center">
                <div class="text-[9px] sm:text-[10px] font-bold text-amber-600 uppercase tracking-tight">En attente</div>
                <div class="text-lg sm:text-2xl font-heading font-extrabold text-amber-600 mt-0.5 sm:mt-1">{{ $statsAujourdhui['en_attente'] }}</div>
            </div>
            <div class="bg-white p-2 sm:p-3.5 rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-card text-center">
                <div class="text-[9px] sm:text-[10px] font-bold text-brand-600 uppercase tracking-tight">Arrivés</div>
                <div class="text-lg sm:text-2xl font-heading font-extrabold text-brand-600 mt-0.5 sm:mt-1">{{ $statsAujourdhui['arrives'] }}</div>
            </div>
            <div class="bg-white p-2 sm:p-3.5 rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-card text-center">
                <div class="text-[9px] sm:text-[10px] font-bold text-indigo-600 uppercase tracking-tight">Effectués</div>
                <div class="text-lg sm:text-2xl font-heading font-extrabold text-indigo-600 mt-0.5 sm:mt-1">{{ $statsAujourdhui['effectues'] }}</div>
            </div>
            <div class="bg-white p-2 sm:p-3.5 rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-card text-center">
                <div class="text-[9px] sm:text-[10px] font-bold text-rose-600 uppercase tracking-tight">Annulés</div>
                <div class="text-lg sm:text-2xl font-heading font-extrabold text-rose-600 mt-0.5 sm:mt-1">{{ $statsAujourdhui['annules'] }}</div>
            </div>
            <div class="bg-white p-2 sm:p-3.5 rounded-xl sm:rounded-2xl border border-slate-200/80 shadow-card text-center">
                <div class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-tight">Absents</div>
                <div class="text-lg sm:text-2xl font-heading font-extrabold text-slate-500 mt-0.5 sm:mt-1">{{ $statsAujourdhui['absents'] }}</div>
            </div>
        </div>
    </div>

    <!-- 2. Compteurs Globaux Établissement -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card flex flex-col justify-between space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Médecins Actifs</div>
                    <div class="text-2xl font-heading font-extrabold text-slate-900 mt-1">{{ $globalStats['medecins'] }}</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600">
                    <i data-lucide="stethoscope" class="w-4 h-4"></i>
                </div>
            </div>
            <a href="{{ route('admin.medecins.index') }}" class="text-xs font-bold text-brand-600 hover:underline flex items-center gap-1">
                <span>Gérer l'équipe</span>
                <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card flex flex-col justify-between space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Patients Inscrits</div>
                    <div class="text-2xl font-heading font-extrabold text-slate-900 mt-1">{{ $globalStats['patients'] }}</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <a href="{{ route('admin.patients.index') }}" class="text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
                <span>Voir le registre</span>
                <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card flex flex-col justify-between space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Spécialités</div>
                    <div class="text-2xl font-heading font-extrabold text-slate-900 mt-1">{{ $globalStats['specialites'] }}</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                </div>
            </div>
            <a href="{{ route('admin.specialites.index') }}" class="text-xs font-bold text-brand-600 hover:underline flex items-center gap-1">
                <span>Pôles médicaux</span>
                <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card flex flex-col justify-between space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Consultations</div>
                    <div class="text-2xl font-heading font-extrabold text-slate-900 mt-1">{{ $globalStats['total_rdv'] }}</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-600">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                </div>
            </div>
            <a href="{{ route('admin.statistiques') }}" class="text-xs font-bold text-brand-600 hover:underline flex items-center gap-1">
                <span>Analyser</span>
                <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        </div>
    </div>

    <!-- 3. Section 2 colonnes : Derniers RDV + Répartition Spécialités -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Derniers rendez-vous -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-heading font-extrabold text-slate-900">Derniers Rendez-vous Enregistrés</h2>
                <a href="{{ route('secretaire.guichet') }}" class="text-xs font-bold text-brand-600 hover:underline">Vue Guichet &rarr;</a>
            </div>

            <!-- 1. Vue Mobile pour Smartphones (< 640px) -->
            <div class="block sm:hidden space-y-2.5">
                @foreach($derniersRdv as $rdv)
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800">
                                {{ \Carbon\Carbon::parse($rdv->date_rdv)->format('d/m/Y') }} à {{ substr($rdv->heure_rdv, 0, 5) }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $rdv->statut_badge['bg'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $rdv->statut_badge['dot'] }}"></span>
                                {{ $rdv->statut_badge['label'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-900">{{ $rdv->patient->user->full_name }}</span>
                            <span class="font-mono text-[10px] text-slate-400">{{ $rdv->reference_rdv }}</span>
                        </div>
                        <div class="text-[11px] text-slate-500 flex items-center justify-between pt-1 border-t border-slate-200/50">
                            <span>Dr. {{ $rdv->medecin->nom_complet }}</span>
                            <span class="text-brand-600 font-medium">{{ $rdv->specialite->nom }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- 2. Vue Tableau pour Ordinateurs (>= 640px) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">Date/Heure</th>
                            <th class="py-3 px-3">Réf.</th>
                            <th class="py-3 px-3">Patient</th>
                            <th class="py-3 px-3">Médecin</th>
                            <th class="py-3 px-3">Spécialité</th>
                            <th class="py-3 px-3">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($derniersRdv as $rdv)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-3 font-semibold text-slate-900">
                                    {{ \Carbon\Carbon::parse($rdv->date_rdv)->format('d/m/Y') }} à {{ substr($rdv->heure_rdv, 0, 5) }}
                                </td>
                                <td class="py-3.5 px-3 font-mono text-slate-500 text-[11px]">
                                    {{ $rdv->reference_rdv }}
                                </td>
                                <td class="py-3.5 px-3 font-bold text-slate-800">
                                    {{ $rdv->patient->user->full_name }}
                                </td>
                                <td class="py-3.5 px-3 font-medium text-slate-700">
                                    {{ $rdv->medecin->nom_complet }}
                                </td>
                                <td class="py-3.5 px-3 text-slate-500">
                                    {{ $rdv->specialite->nom }}
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold border {{ $rdv->statut_badge['bg'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $rdv->statut_badge['dot'] }}"></span>
                                        {{ $rdv->statut_badge['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Répartition par Spécialité -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
            <h2 class="text-base font-heading font-extrabold text-slate-900">Répartition par Spécialité</h2>
            <div class="divide-y divide-slate-100">
                @foreach($specialitesStats as $spe)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <i data-lucide="activity" class="w-3.5 h-3.5 text-brand-600"></i>
                            <span class="font-bold text-slate-800">{{ $spe->nom }}</span>
                        </div>
                        <span class="font-mono font-bold text-slate-700">{{ $spe->rendez_vous_count }} RDV</span>
                    </div>
                @endforeach
            </div>
            <div class="pt-2">
                <a href="{{ route('admin.statistiques') }}" class="block text-center py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-xs font-bold text-slate-700 border border-slate-200 transition">
                    Voir toutes les statistiques &rarr;
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
