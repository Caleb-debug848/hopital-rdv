@extends('layouts.app')

@section('content')
<div class="space-y-8">

    <!-- En-tête Docteur Professionnel -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center font-heading font-bold text-lg shadow-sm">
                <i data-lucide="stethoscope" class="w-6 h-6 text-brand-400"></i>
            </div>
            <div>
                <h1 class="text-xl font-heading font-extrabold text-slate-900">Dr. {{ $medecin->nom_complet }}</h1>
                <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                    <span class="font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded border border-brand-100">{{ $medecin->specialite->nom }}</span>
                    <span>•</span>
                    <span>{{ $medecin->service ?? 'Service Hospitalier' }}</span>
                    <span>•</span>
                    <span class="flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                        {{ $medecin->bureau ?? 'Bureau Consultations' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="w-full sm:w-auto">
            <a href="{{ route('medecin.planning') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition min-h-[44px]">
                <i data-lucide="calendar" class="w-4 h-4 text-brand-400"></i>
                Consulter mon Planning
            </a>
        </div>
    </div>

    <!-- Statistiques du jour -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">RDV Aujourd'hui</div>
                <div class="text-2xl font-heading font-extrabold text-slate-900 mt-1">{{ $stats['total_aujourdhui'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500">
                <i data-lucide="calendar" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-brand-600 uppercase tracking-wider">Patients Arrivés</div>
                <div class="text-2xl font-heading font-extrabold text-brand-600 mt-1">{{ $stats['arrives_aujourdhui'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Consultations Faites</div>
                <div class="text-2xl font-heading font-extrabold text-emerald-600 mt-1">{{ $stats['effectues_aujourdhui'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-purple-600 uppercase tracking-wider">À Venir cette semaine</div>
                <div class="text-2xl font-heading font-extrabold text-purple-600 mt-1">{{ $stats['a_venir_semaine'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600">
                <i data-lucide="calendar-days" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Prochain Patient Highlight -->
    @if($prochainPatient)
        <div class="bg-slate-900 text-white rounded-2xl p-6 shadow-md border border-slate-800">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-brand-500/20 border border-brand-400/30 text-brand-300 text-[11px] font-bold uppercase tracking-wider">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-brand-400"></i>
                        Prochain Patient Attendu
                    </div>
                    <div class="text-xl sm:text-2xl font-heading font-extrabold tracking-tight">
                        {{ $prochainPatient->patient->user->full_name }}
                    </div>
                    <div class="text-xs text-slate-300 flex items-center gap-3 flex-wrap">
                        <span class="flex items-center gap-1">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-brand-400"></i>
                            Heure : <strong class="text-white">{{ substr($prochainPatient->heure_rdv, 0, 5) }}</strong>
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                            {{ $prochainPatient->patient->user->telephone }}
                        </span>
                        <span>•</span>
                        <span>Motif : {{ $prochainPatient->motif ?? 'Consultation standard' }}</span>
                    </div>
                </div>

                <!-- Actions Rapides pour ce patient -->
                <div class="flex items-center gap-2 flex-wrap">
                    @if($prochainPatient->statut !== 'arrive')
                        <form action="{{ route('medecin.rdv.status', $prochainPatient->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="statut" value="arrive">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs transition shadow-xs">
                                Marquer Arrivé
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('medecin.rdv.status', $prochainPatient->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="statut" value="termine">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-xs flex items-center gap-1.5">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            Consultation Terminée
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Tableau du Planning Aujourd'hui -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden space-y-4 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-heading font-extrabold text-slate-900">Planning des Consultations — Aujourd'hui</h2>
                <p class="text-xs text-slate-500">{{ \Carbon\Carbon::today()->translatedFormat('l d F Y') }}</p>
            </div>
        </div>

        @if($rdvAujourdhui->isEmpty())
            <div class="p-8 text-center text-xs text-slate-400">
                Aucun rendez-vous prévu pour aujourd'hui.
            </div>
        @else
            <!-- 1. Vue Mobile pour Smartphones (< 640px) -->
            <div class="block sm:hidden space-y-3">
                @foreach($rdvAujourdhui as $rdv)
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-mono font-bold text-xs text-slate-900 shadow-2xs">
                                {{ substr($rdv->heure_rdv, 0, 5) }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold border {{ $rdv->statut_badge['bg'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $rdv->statut_badge['dot'] }}"></span>
                                {{ $rdv->statut_badge['label'] }}
                            </span>
                        </div>

                        <div>
                            <div class="font-heading font-extrabold text-sm text-slate-900">{{ $rdv->patient->user->full_name }}</div>
                            <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                <a href="tel:{{ $rdv->patient->user->telephone }}" class="text-brand-600 font-mono font-bold inline-flex items-center gap-1 hover:underline">
                                    <i data-lucide="phone" class="w-3 h-3"></i>
                                    {{ $rdv->patient->user->telephone }}
                                </a>
                                <span>•</span>
                                <span class="font-mono text-slate-400">{{ $rdv->reference_rdv }}</span>
                            </div>
                            @if($rdv->motif)
                                <p class="text-xs text-slate-600 mt-1.5 bg-white p-2 rounded-lg border border-slate-200/60">
                                    <span class="font-bold text-slate-500">Motif :</span> {{ $rdv->motif }}
                                </p>
                            @endif
                        </div>

                        <!-- Actions Tactiles Rapides -->
                        <div class="flex items-center gap-2 pt-1 border-t border-slate-200/60">
                            @if($rdv->statut !== 'termine')
                                <form action="{{ route('medecin.rdv.status', $rdv->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="statut" value="termine">
                                    <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 active:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 min-h-[40px] shadow-xs">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        Terminer
                                    </button>
                                </form>
                            @endif
                            @if($rdv->statut !== 'absent' && $rdv->statut !== 'termine')
                                <form action="{{ route('medecin.rdv.status', $rdv->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="statut" value="absent">
                                    <button type="submit" class="px-3.5 py-2.5 rounded-xl bg-slate-200 active:bg-slate-300 text-slate-700 font-bold text-xs flex items-center justify-center min-h-[40px]">
                                        Absent
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- 2. Vue Tableau pour Ordinateurs & Tablettes (>= 640px) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">Heure</th>
                            <th class="py-3 px-3">Réf.</th>
                            <th class="py-3 px-3">Patient</th>
                            <th class="py-3 px-3">Téléphone</th>
                            <th class="py-3 px-3">Motif</th>
                            <th class="py-3 px-3">Statut</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rdvAujourdhui as $rdv)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-3 font-bold text-slate-900">
                                    {{ substr($rdv->heure_rdv, 0, 5) }}
                                </td>
                                <td class="py-3.5 px-3 font-mono text-slate-500">
                                    {{ $rdv->reference_rdv }}
                                </td>
                                <td class="py-3.5 px-3 font-bold text-slate-800">
                                    {{ $rdv->patient->user->full_name }}
                                </td>
                                <td class="py-3.5 px-3 text-slate-500">
                                    {{ $rdv->patient->user->telephone }}
                                </td>
                                <td class="py-3.5 px-3 text-slate-600 max-w-xs truncate">
                                    {{ $rdv->motif ?? '—' }}
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold border {{ $rdv->statut_badge['bg'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $rdv->statut_badge['dot'] }}"></span>
                                        {{ $rdv->statut_badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        @if($rdv->statut !== 'termine')
                                            <form action="{{ route('medecin.rdv.status', $rdv->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="statut" value="termine">
                                                <button type="submit" title="Marquer terminé" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition">
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($rdv->statut !== 'absent' && $rdv->statut !== 'termine')
                                            <form action="{{ route('medecin.rdv.status', $rdv->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="statut" value="absent">
                                                <button type="submit" title="Marquer absent" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                                                    <i data-lucide="user-x" class="w-4 h-4"></i>
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
        @endif
    </div>

</div>
@endsection
