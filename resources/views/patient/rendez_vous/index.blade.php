@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight">Mes Rendez-vous</h1>
            <p class="text-xs text-slate-500">Historique de vos consultations et gestion de vos convocations médicales</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('patient.historique.export') }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs transition" title="Télécharger ou imprimer tout mon dossier">
                <i data-lucide="file-down" class="w-4 h-4 text-blue-600"></i>
                <span>Exporter mon dossier PDF</span>
            </a>

            <a href="{{ route('patient.rendez-vous.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Nouveau RDV</span>
            </a>
        </div>
    </div>

    <!-- Onglets de filtre épurés -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <a href="{{ route('patient.rendez-vous.index', ['tab' => 'a_venir']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'a_venir' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
            À venir
        </a>
        <a href="{{ route('patient.rendez-vous.index', ['tab' => 'historique']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'historique' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
            Historique & Effectués
        </a>
        <a href="{{ route('patient.rendez-vous.index', ['tab' => 'annules']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $tab === 'annules' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
            Annulés
        </a>
    </div>

    <!-- Liste des rendez-vous -->
    @if($rendezVous->isEmpty())
        <div class="bg-white rounded-2xl p-12 border border-slate-200/80 shadow-card text-center space-y-3">
            <div class="w-12 h-12 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto border border-slate-100">
                <i data-lucide="calendar-x" class="w-6 h-6"></i>
            </div>
            <div class="text-sm font-bold text-slate-700">Aucun rendez-vous dans cette section</div>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">Vous n'avez pas de rendez-vous correspondant au filtre actif.</p>
            <a href="{{ route('patient.rendez-vous.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                Prendre rendez-vous
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($rendezVous as $rdv)
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card hover:border-slate-300 transition flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-xl bg-slate-100 border border-slate-200 text-slate-800 flex flex-col items-center justify-center font-bold text-xs flex-shrink-0">
                            <span class="text-base font-extrabold text-slate-900 leading-none">{{ \Carbon\Carbon::parse($rdv->date_rdv)->format('d') }}</span>
                            <span class="text-[10px] uppercase font-bold text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($rdv->date_rdv)->translatedFormat('M') }}</span>
                        </div>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-heading font-bold text-sm text-slate-900">{{ $rdv->medecin->nom_complet }}</span>
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $rdv->statut_badge['bg'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $rdv->statut_badge['dot'] }}"></span>
                                    {{ $rdv->statut_badge['label'] }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 flex items-center gap-3 flex-wrap">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="activity" class="w-3 h-3 text-slate-400"></i>
                                    {{ $rdv->specialite->nom }}
                                </span>
                                <span>•</span>
                                <span class="flex items-center gap-1 font-semibold text-slate-700">
                                    <i data-lucide="clock" class="w-3 h-3 text-slate-400"></i>
                                    {{ substr($rdv->heure_rdv, 0, 5) }}
                                </span>
                                <span>•</span>
                                <span class="flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                    {{ $rdv->medecin->bureau ?? 'Hôpital Central' }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono">
                                Réf: <strong class="text-slate-600">{{ $rdv->reference_rdv }}</strong> {{ $rdv->motif ? '— Motif : ' . $rdv->motif : '' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end md:self-center">
                        <a href="{{ route('patient.rendez-vous.attestation', $rdv->id) }}" target="_blank" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition" title="Télécharger l'attestation PDF">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('patient.rendez-vous.show', $rdv->id) }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center gap-1.5">
                            <i data-lucide="eye" class="w-3.5 h-3.5 text-blue-400"></i>
                            <span>Consulter</span>
                        </a>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="pt-4">
                {{ $rendezVous->links() }}
            </div>
        </div>
    @endif

</div>
@endsection
