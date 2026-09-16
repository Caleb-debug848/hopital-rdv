@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 py-2" x-data="{ cancelModal: false }">

    <!-- Bouton retour -->
    <a href="{{ route('patient.rendez-vous.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:underline">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Retour à mes rendez-vous</span>
    </a>

    <!-- Carte principale du RDV -->
    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-card space-y-6">

        <!-- En-tête Réf & Statut -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <span class="text-[10px] font-mono uppercase font-bold text-slate-400">Rendez-vous Hospitalier</span>
                <h1 class="text-xl sm:text-2xl font-heading font-extrabold text-slate-900 tracking-tight">{{ $rendezVous->reference_rdv }}</h1>
                <div class="text-xs text-slate-500 mt-0.5">Enregistré le {{ $rendezVous->created_at->format('d/m/Y à H:i') }}</div>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-bold border {{ $rendezVous->statut_badge['bg'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $rendezVous->statut_badge['dot'] }}"></span>
                    {{ $rendezVous->statut_badge['label'] }}
                </span>
            </div>
        </div>

        <!-- Stepper Visuel de Suivi du Statut -->
        <div class="space-y-2">
            <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Parcours de la consultation</div>
            <div class="grid grid-cols-4 gap-2 text-center text-xs">
                @php
                    $steps = [
                        'en_attente' => 1,
                        'confirme' => 2,
                        'arrive' => 3,
                        'termine' => 4,
                        'annule' => 0,
                        'absent' => 0,
                    ];
                    $currentStep = $steps[$rendezVous->statut] ?? 1;
                @endphp

                <div class="p-2.5 rounded-xl border transition {{ $currentStep >= 1 ? 'bg-slate-900 text-white font-bold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    1. En attente
                </div>
                <div class="p-2.5 rounded-xl border transition {{ $currentStep >= 2 ? 'bg-emerald-50 border-emerald-300 text-emerald-800 font-bold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    2. Confirmé
                </div>
                <div class="p-2.5 rounded-xl border transition {{ $currentStep >= 3 ? 'bg-blue-50 border-blue-300 text-blue-800 font-bold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    3. Patient Arrivé
                </div>
                <div class="p-2.5 rounded-xl border transition {{ $currentStep >= 4 ? 'bg-indigo-50 border-indigo-300 text-indigo-800 font-bold' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    4. Effectué
                </div>
            </div>
        </div>

        <!-- Détails de la consultation -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Praticien & Service</div>
                <div class="font-bold text-sm text-slate-900">{{ $rendezVous->medecin->nom_complet }}</div>
                <div class="text-xs text-slate-600">{{ $rendezVous->specialite->nom }}</div>
                <div class="text-xs text-slate-500">{{ $rendezVous->medecin->bureau ?? 'Hôpital Central' }}</div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Date & Heure</div>
                <div class="font-bold text-sm text-slate-900">{{ \Carbon\Carbon::parse($rendezVous->date_rdv)->translatedFormat('l d F Y') }}</div>
                <div class="text-xs font-bold text-emerald-700">À {{ substr($rendezVous->heure_rdv, 0, 5) }}</div>
                <div class="text-[11px] text-slate-400">Durée : 30 minutes</div>
            </div>
        </div>

        @if($rendezVous->motif)
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-1 text-xs">
                <div class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Motif de consultation</div>
                <div class="text-slate-800">{{ $rendezVous->motif }}</div>
            </div>
        @endif

        @if($rendezVous->statut === 'annule')
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900 space-y-1">
                <div class="font-bold flex items-center gap-1.5">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    <span>Rendez-vous annulé (par {{ $rendezVous->annule_par ?? 'inconnu' }})</span>
                </div>
                <p>{{ $rendezVous->notes_annulation ?? 'Aucun motif d\'annulation précisé.' }}</p>
            </div>
        @endif

        <!-- Barre d'outils et Actions Avancées -->
        <div class="pt-6 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Bouton Attestation PDF / Impression -->
                <a href="{{ route('patient.rendez-vous.attestation', $rendezVous->id) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-blue-400"></i>
                    <span>Attestation de RDV (PDF)</span>
                </a>

                <!-- Bouton Envoi Rappel SMS & Email -->
                @if(in_array($rendezVous->statut, ['en_attente', 'confirme']))
                    <form action="{{ route('patient.rendez-vous.rappel', $rendezVous->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-200 transition" title="Recevoir un rappel par SMS et email">
                            <i data-lucide="bell-ring" class="w-3.5 h-3.5 text-blue-600"></i>
                            <span>Rappel SMS / Email</span>
                        </button>
                    </form>
                @endif
            </div>

            <!-- Annulation si RDV actif -->
            @if(in_array($rendezVous->statut, ['en_attente', 'confirme']))
                <button type="button" @click="cancelModal = true" class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs border border-rose-200 transition">
                    Annuler ce créneau
                </button>
            @endif
        </div>

    </div>

    <!-- Modal d'annulation sans emoji -->
    <div x-show="cancelModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full space-y-4 shadow-xl border border-slate-200" @click.outside="cancelModal = false">
            <div class="text-center space-y-2">
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto border border-rose-100">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <h3 class="text-base font-heading font-extrabold text-slate-900">Confirmer l'annulation</h3>
                <p class="text-xs text-slate-500">Votre créneau sera libéré et proposé en priorité aux patients en liste d'attente.</p>
            </div>

            <form action="{{ route('patient.rendez-vous.cancel', $rendezVous->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Motif de l'annulation (facultatif)</label>
                    <textarea name="notes_annulation" rows="2" placeholder="Ex: Empêchement professionnel, indisponibilité..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" @click="cancelModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                        Garder le RDV
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-xs transition">
                        Confirmer l'annulation
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
