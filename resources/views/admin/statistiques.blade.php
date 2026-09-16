@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight">Rapports & Statistiques</h1>
            <p class="text-xs text-slate-500">Analyse des flux de consultations, taux de présence et répartition médicale par période</p>
        </div>
    </div>

    <!-- Filtre par Période -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 flex-wrap text-xs">
            <span class="font-bold text-slate-400 uppercase tracking-wider text-[10px] mr-2">Période :</span>
            <a href="{{ route('admin.statistiques', ['periode' => 'aujourdhui']) }}" class="px-3 py-1.5 rounded-xl font-bold transition {{ $periode === 'aujourdhui' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Aujourd'hui
            </a>
            <a href="{{ route('admin.statistiques', ['periode' => 'cette_semaine']) }}" class="px-3 py-1.5 rounded-xl font-bold transition {{ $periode === 'cette_semaine' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Cette semaine
            </a>
            <a href="{{ route('admin.statistiques', ['periode' => 'ce_mois']) }}" class="px-3 py-1.5 rounded-xl font-bold transition {{ $periode === 'ce_mois' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Ce mois
            </a>
            <a href="{{ route('admin.statistiques', ['periode' => 'cette_annee']) }}" class="px-3 py-1.5 rounded-xl font-bold transition {{ $periode === 'cette_annee' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Année complète
            </a>
        </div>
        <div class="text-xs font-semibold text-slate-500">
            Total période : <strong class="text-slate-900 font-extrabold text-sm">{{ $totalRdv }} rendez-vous</strong>
        </div>
    </div>

    <!-- Répartition par Statut -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card text-center">
            <div class="text-[10px] font-bold text-emerald-600 uppercase">Confirmés</div>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $statutsBreakdown['confirme'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card text-center">
            <div class="text-[10px] font-bold text-brand-600 uppercase">Arrivés</div>
            <div class="text-2xl font-extrabold text-brand-600 mt-1">{{ $statutsBreakdown['arrive'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card text-center">
            <div class="text-[10px] font-bold text-indigo-600 uppercase">Effectués</div>
            <div class="text-2xl font-extrabold text-indigo-600 mt-1">{{ $statutsBreakdown['termine'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card text-center">
            <div class="text-[10px] font-bold text-amber-600 uppercase">En attente</div>
            <div class="text-2xl font-extrabold text-amber-600 mt-1">{{ $statutsBreakdown['en_attente'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card text-center">
            <div class="text-[10px] font-bold text-rose-600 uppercase">Annulés</div>
            <div class="text-2xl font-extrabold text-rose-600 mt-1">{{ $statutsBreakdown['annule'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase">Absents</div>
            <div class="text-2xl font-extrabold text-slate-500 mt-1">{{ $statutsBreakdown['absent'] }}</div>
        </div>
    </div>

    <!-- 2 Tableaux : Par Spécialité & Par Médecin -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Par Spécialité -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
            <h2 class="text-base font-heading font-extrabold text-slate-900">Consultations par Spécialité</h2>
            <div class="divide-y divide-slate-100">
                @foreach($bySpecialite as $spe)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                            <span class="font-bold text-slate-900">{{ $spe->nom }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-slate-900">{{ $spe->rendez_vous_count }} RDV</span>
                            <span class="text-slate-400 w-12 text-right">
                                {{ $totalRdv > 0 ? round(($spe->rendez_vous_count / $totalRdv) * 100, 1) : 0 }}%
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Par Médecin -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
            <h2 class="text-base font-heading font-extrabold text-slate-900">Consultations par Médecin</h2>
            <div class="divide-y divide-slate-100">
                @foreach($byMedecin as $med)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-slate-900">{{ $med->nom_complet }}</div>
                            <div class="text-[11px] text-slate-400">{{ $med->specialite->nom }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-brand-600">{{ $med->rendez_vous_count }} RDV</span>
                            <span class="text-slate-400 w-12 text-right">
                                {{ $totalRdv > 0 ? round(($med->rendez_vous_count / $totalRdv) * 100, 1) : 0 }}%
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
@endsection
