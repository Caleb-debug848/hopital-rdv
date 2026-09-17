@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="bar-chart-3" class="w-5 h-5 text-brand-600"></i>
                Centre de Rapports & Pilotage Hospitalier
            </h1>
            <p class="text-xs text-slate-500">Analyse de l'affluence, absentéisme, activité médicale et exports certifiés pour la Direction</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.statistiques.export', ['periode' => $periode]) }}"
               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition inline-flex items-center gap-2 shadow-sm">
                <i data-lucide="download" class="w-4 h-4"></i>
                Exporter en CSV / Excel (1 Clic)
            </a>
        </div>
    </div>

    <!-- Filtre par Période & Synthèse -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2 flex-wrap text-xs">
            <span class="font-bold text-slate-400 uppercase tracking-wider text-[10px] mr-1">Période :</span>
            <a href="{{ route('admin.statistiques', ['periode' => 'aujourdhui']) }}" class="px-3 py-1.5 rounded-xl font-bold transition {{ $periode === 'aujourdhui' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Aujourd'hui
            </a>
            <a href="{{ route('admin.statistiques', ['periode' => 'cette_semaine']) }}" class="px-3 py-1.5 rounded-xl font-bold transition {{ $periode === 'cette_semaine' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Cette semaine
            </a>
            <a href="{{ route('admin.statistiques', ['periode' => 'ce_mois']) }}" class="px-3 py-1.5 rounded-xl font-bold transition {{ $periode === 'ce_mois' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Ce mois
            </a>
            <a href="{{ route('admin.statistiques', ['periode' => 'cette_annee']) }}" class="px-3 py-1.5 rounded-xl font-bold transition {{ $periode === 'cette_annee' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                Année complète
            </a>
        </div>

        <div class="flex items-center gap-4 text-xs font-semibold text-slate-500">
            <div>
                Total période : <strong class="text-slate-900 font-extrabold text-sm">{{ $totalRdv }} rendez-vous</strong>
            </div>
            <div class="hidden sm:block text-slate-300">|</div>
            <div class="hidden sm:flex items-center gap-1.5">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                Taux de présence : <strong class="text-emerald-700 font-bold">{{ $tauxPresence }}%</strong>
            </div>
        </div>
    </div>

    <!-- Indicateurs Clés de Direction & Absentéisme -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card space-y-2">
            <div class="flex items-center justify-between text-xs text-slate-500 font-bold">
                <span>Volume Global de Consultations</span>
                <i data-lucide="calendar-check" class="w-4 h-4 text-brand-600"></i>
            </div>
            <div class="text-3xl font-heading font-extrabold text-slate-900">{{ $totalRdv }}</div>
            <div class="text-[11px] text-slate-400">Total des réservations enregistrées sur la période</div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card space-y-2">
            <div class="flex items-center justify-between text-xs text-emerald-700 font-bold">
                <span>Taux d'Assiduité / Présence</span>
                <i data-lucide="check-check" class="w-4 h-4 text-emerald-600"></i>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-heading font-extrabold text-emerald-600">{{ $tauxPresence }}%</span>
                <span class="text-xs text-slate-400">honorés</span>
            </div>
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $tauxPresence }}%"></div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card space-y-2">
            <div class="flex items-center justify-between text-xs text-rose-700 font-bold">
                <span>Taux d'Absentéisme (No-Show)</span>
                <i data-lucide="user-x" class="w-4 h-4 text-rose-600"></i>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-heading font-extrabold text-rose-600">{{ $tauxAbsenteisme }}%</span>
                <span class="text-xs text-slate-400">non honorés</span>
            </div>
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div class="bg-rose-500 h-full rounded-full" style="width: {{ $tauxAbsenteisme }}%"></div>
            </div>
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

    <!-- Section Affluence & Heures de Pointe -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-heading font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-brand-600"></i>
                    Analyse de l'Affluence & Heures de Pointe
                </h2>
                <p class="text-xs text-slate-500">Distribution horaire des consultations pour optimiser la présence du personnel d'accueil et soignant</p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-400">Fuseau : Africa/Douala</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-5 gap-4 pt-2">
            @php
                $maxAffluence = max(array_values($creneauxHoraires) ?: [1]);
                if ($maxAffluence === 0) $maxAffluence = 1;
            @endphp

            @foreach($creneauxHoraires as $creneau => $nb)
                @php
                    $pourcentage = $totalRdv > 0 ? round(($nb / $totalRdv) * 100) : 0;
                    $isPeak = $nb > 0 && $nb === $maxAffluence;
                @endphp
                <div class="p-4 rounded-xl border {{ $isPeak ? 'border-brand-300 bg-brand-50/40' : 'border-slate-100 bg-slate-50/60' }} space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-xs font-bold {{ $isPeak ? 'text-brand-900' : 'text-slate-700' }}">{{ $creneau }}</span>
                        @if($isPeak)
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-brand-600 text-white uppercase">Pointe</span>
                        @endif
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-heading font-extrabold {{ $isPeak ? 'text-brand-700' : 'text-slate-900' }}">{{ $nb }}</span>
                        <span class="text-xs text-slate-400 font-medium">{{ $pourcentage }}%</span>
                    </div>
                    <div class="w-full bg-slate-200/80 h-2 rounded-full overflow-hidden">
                        <div class="{{ $isPeak ? 'bg-brand-600' : 'bg-slate-400' }} h-full rounded-full transition-all duration-300" style="width: {{ $totalRdv > 0 ? ($nb / $maxAffluence) * 100 : 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 2 Tableaux : Par Spécialité & Par Médecin -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Par Spécialité -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
            <h2 class="text-base font-heading font-extrabold text-slate-900 flex items-center gap-2">
                <i data-lucide="stethoscope" class="w-4 h-4 text-brand-600"></i>
                Consultations par Spécialité
            </h2>
            <div class="divide-y divide-slate-100">
                @forelse($bySpecialite as $spe)
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
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">Aucune consultation sur cette période</div>
                @endforelse
            </div>
        </div>

        <!-- Par Médecin -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
            <h2 class="text-base font-heading font-extrabold text-slate-900 flex items-center gap-2">
                <i data-lucide="user-round" class="w-4 h-4 text-brand-600"></i>
                Consultations par Médecin
            </h2>
            <div class="divide-y divide-slate-100">
                @forelse($byMedecin as $med)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-slate-900">Dr. {{ $med->nom_complet }}</div>
                            <div class="text-[11px] text-slate-400">{{ $med->specialite->nom }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-brand-600">{{ $med->rendez_vous_count }} RDV</span>
                            <span class="text-slate-400 w-12 text-right">
                                {{ $totalRdv > 0 ? round(($med->rendez_vous_count / $totalRdv) * 100, 1) : 0 }}%
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">Aucune consultation sur cette période</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
