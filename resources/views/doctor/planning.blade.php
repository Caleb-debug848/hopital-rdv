@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ indispoModal: false }">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight">Planning des Consultations</h1>
            <p class="text-xs text-slate-500">Consultez votre calendrier de consultation et déclarez vos périodes d'absence</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" @click="indispoModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
                <i data-lucide="calendar-off" class="w-4 h-4 text-brand-400"></i>
                Déclarer une absence
            </button>
        </div>
    </div>

    <!-- Sélecteur de date -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Date :</span>
            <form action="{{ route('medecin.planning') }}" method="GET" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                       class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </form>
        </div>
        <div class="text-xs text-slate-500">
            {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l d F Y') }} — <strong class="text-slate-800">{{ count($rdvs) }} rendez-vous</strong>
        </div>
    </div>

    <!-- Liste des rendez-vous de la date -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
        @if($rdvs->isEmpty())
            <div class="py-12 text-center space-y-2">
                <div class="w-12 h-12 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto border border-slate-100">
                    <i data-lucide="calendar" class="w-6 h-6"></i>
                </div>
                <div class="text-sm font-bold text-slate-700">Aucune consultation prévue pour le {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</div>
                <p class="text-xs text-slate-400">Aucun patient n'a réservé de créneau sur cette date.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($rdvs as $rdv)
                    <div class="p-4 rounded-xl border border-slate-200 hover:border-slate-300 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-12 rounded-xl bg-slate-100 text-slate-800 font-bold text-xs flex items-center justify-center border border-slate-200">
                                {{ substr($rdv->heure_rdv, 0, 5) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-heading font-bold text-slate-900 text-xs sm:text-sm">{{ $rdv->patient->user->full_name }}</span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $rdv->statut_badge['bg'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $rdv->statut_badge['dot'] }}"></span>
                                        {{ $rdv->statut_badge['label'] }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Réf: {{ $rdv->reference_rdv }} • Tél : {{ $rdv->patient->user->telephone }} {{ $rdv->motif ? '• ' . $rdv->motif : '' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center">
                            @if($rdv->statut !== 'termine')
                                <form action="{{ route('medecin.rdv.status', $rdv->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="statut" value="termine">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold border border-emerald-200 transition flex items-center gap-1">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        Terminé
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modal Déclaration d'Absence -->
    <div x-show="indispoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full space-y-4 shadow-xl border border-slate-200" @click.outside="indispoModal = false">
            <div class="text-center space-y-1">
                <h3 class="text-base font-heading font-extrabold text-slate-900">Déclarer une absence / Congé</h3>
                <p class="text-xs text-slate-500">Les créneaux sur cette période seront automatiquement bloqués</p>
            </div>

            <form action="{{ route('medecin.indisponibilite.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Date Début *</label>
                        <input type="date" name="date_debut" required min="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Date Fin *</label>
                        <input type="date" name="date_fin" required min="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Motif</label>
                    <input type="text" name="motif" placeholder="Ex: Congé annuel, Formation, Mission..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="indispoModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
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
