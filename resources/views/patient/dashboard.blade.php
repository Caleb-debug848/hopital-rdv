@extends('layouts.app')

@section('content')
<div class="space-y-8">

    <!-- En-tête Patient Professionnel -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 sm:p-6 rounded-2xl border border-slate-200/80 shadow-card">
        <div class="flex items-center gap-3.5 sm:gap-4">
            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center font-heading font-bold text-base sm:text-lg shadow-sm shrink-0">
                {{ substr(Auth::user()->prenom, 0, 1) }}{{ substr(Auth::user()->nom, 0, 1) }}
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="text-lg sm:text-xl font-heading font-extrabold text-slate-900 truncate">Bonjour, {{ Auth::user()->prenom }} {{ Auth::user()->nom }}</h1>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs text-slate-500 mt-1">
                    <span class="font-mono font-semibold bg-slate-100 px-2 py-0.5 rounded text-slate-700 text-[11px]">Dossier : {{ $patient->numero_patient }}</span>
                    <span class="hidden sm:inline">•</span>
                    <span class="flex items-center gap-1 text-[11px]">
                        <i data-lucide="phone" class="w-3 h-3 text-slate-400"></i>
                        {{ Auth::user()->telephone }}
                    </span>
                </div>
            </div>
        </div>
        <div>
            <a href="{{ route('patient.rendez-vous.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition min-h-[44px]">
                <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                Prendre un Rendez-vous
            </a>
        </div>
    </div>

    <!-- Prochain Rendez-vous en vedette (si existant) -->
    @if($prochainRdv)
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-2xl p-4 sm:p-6 shadow-md border border-slate-800 relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5 sm:gap-6">
                <div class="space-y-2.5">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-brand-500/20 border border-brand-400/30 text-brand-300 text-[11px] font-bold uppercase tracking-wider">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-brand-400"></i>
                        Prochain Rendez-vous
                    </div>
                    <div class="text-xl sm:text-2xl font-heading font-extrabold tracking-tight">
                        {{ \Carbon\Carbon::parse($prochainRdv->date_rdv)->translatedFormat('l d F Y') }} à {{ substr($prochainRdv->heure_rdv, 0, 5) }}
                    </div>
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-xs text-slate-300">
                        <span class="font-semibold text-white flex items-center gap-1.5">
                            <i data-lucide="stethoscope" class="w-3.5 h-3.5 text-brand-400"></i>
                            {{ $prochainRdv->medecin->nom_complet }}
                        </span>
                        <span>•</span>
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-slate-400"></i>
                            {{ $prochainRdv->specialite->nom }}
                        </span>
                        <span>•</span>
                        <span class="text-slate-400 flex items-center gap-1">
                            <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                            {{ $prochainRdv->medecin->bureau ?? 'Service Consultations' }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('patient.rendez-vous.show', $prochainRdv->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/20 transition backdrop-blur-xs min-h-[40px]">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                        Voir détails & Réf.
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Cartes KPI Métriques -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-4">
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-card flex items-center justify-between">
            <div>
                <div class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider">RDV Totaux</div>
                <div class="text-xl sm:text-2xl font-heading font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</div>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                <i data-lucide="calendar" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-card flex items-center justify-between">
            <div>
                <div class="text-[10px] sm:text-[11px] font-bold text-brand-600 uppercase tracking-wider">À Venir</div>
                <div class="text-xl sm:text-2xl font-heading font-extrabold text-brand-600 mt-1">{{ $stats['a_venir'] }}</div>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600 shrink-0">
                <i data-lucide="calendar-clock" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-card flex items-center justify-between">
            <div>
                <div class="text-[10px] sm:text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Effectués</div>
                <div class="text-xl sm:text-2xl font-heading font-extrabold text-emerald-600 mt-1">{{ $stats['effectues'] }}</div>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <i data-lucide="check-circle" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-card flex items-center justify-between">
            <div>
                <div class="text-[10px] sm:text-[11px] font-bold text-rose-600 uppercase tracking-wider">Annulés</div>
                <div class="text-xl sm:text-2xl font-heading font-extrabold text-rose-600 mt-1">{{ $stats['annules'] }}</div>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                <i data-lucide="x-circle" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
        </div>
    </div>
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Annulés</div>
                <div class="text-2xl font-heading font-extrabold text-rose-600 mt-1">{{ $stats['annules'] }}</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600">
                <i data-lucide="x-circle" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Section Prise de Rendez-vous Rapide par Spécialité (Quick Booking) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-heading font-extrabold text-slate-900">Prendre Rendez-vous par Spécialité</h2>
                <p class="text-xs text-slate-500">Sélectionnez le service médical souhaité pour afficher les créneaux disponibles</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($specialites->take(4) as $spe)
                <a href="{{ route('patient.rendez-vous.create', ['specialite_id' => $spe->id]) }}" 
                   class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card hover:border-brand-500 hover:shadow-md transition-all group flex flex-col justify-between space-y-4">
                    <div class="flex items-start justify-between">
                        <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-slate-700 flex items-center justify-center group-hover:bg-brand-50 group-hover:text-brand-600 group-hover:border-brand-200 transition">
                            <i data-lucide="activity" class="w-5 h-5 stroke-[2]"></i>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full border border-slate-100">
                            {{ $spe->medecins_count }} méd.
                        </span>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-sm text-slate-900 group-hover:text-brand-600 transition">{{ $spe->nom }}</h3>
                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                            {{ $spe->description ?? 'Consultation spécialisée et suivi personnalisé.' }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Section 2 Colonnes : RDV à venir + Notifications -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Colonne Gauche : Liste RDV à venir -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-heading font-extrabold text-slate-900">Mes Prochains Rendez-vous</h2>
                <a href="{{ route('patient.rendez-vous.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1">
                    <span>Tout voir</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            @if($rdvAvenir->isEmpty())
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-card text-center space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto border border-slate-100">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <div class="text-xs font-bold text-slate-700">Aucun rendez-vous planifié</div>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">Vous n'avez pas de consultation à venir. Choisissez un créneau auprès d'un praticien.</p>
                    <a href="{{ route('patient.rendez-vous.create') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 text-xs font-bold transition min-h-[44px]">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Prendre un rendez-vous
                    </a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($rdvAvenir as $rdv)
                        <div class="bg-white p-3.5 sm:p-5 rounded-2xl border border-slate-200/80 shadow-card hover:border-slate-300 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                            <div class="flex items-start gap-3 sm:gap-4 min-w-0">
                                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex flex-col items-center justify-center font-bold text-xs shrink-0">
                                    <span class="text-xs sm:text-sm font-extrabold text-slate-900 leading-none">{{ \Carbon\Carbon::parse($rdv->date_rdv)->format('d') }}</span>
                                    <span class="text-[9px] uppercase font-bold text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($rdv->date_rdv)->translatedFormat('M') }}</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                        <span class="font-bold text-slate-900 text-xs sm:text-sm truncate">{{ $rdv->medecin->nom_complet }}</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $rdv->statut_badge['bg'] }} shrink-0">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $rdv->statut_badge['dot'] }}"></span>
                                            {{ $rdv->statut_badge['label'] }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-2">
                                        <span>{{ $rdv->specialite->nom }}</span>
                                        <span class="hidden sm:inline">•</span>
                                        <span class="font-semibold text-slate-700">Horaire : {{ substr($rdv->heure_rdv, 0, 5) }}</span>
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                        Réf: {{ $rdv->reference_rdv }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 w-full sm:w-auto">
                                <a href="{{ route('patient.rendez-vous.show', $rdv->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition gap-1.5 min-h-[40px]">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    Détails & Reçu
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Colonne Droite : Dernières Notifications -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-heading font-extrabold text-slate-900">Notifications</h2>
                <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-brand-600 hover:underline">Voir tout</a>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card space-y-3">
                @if($notifications->isEmpty())
                    <p class="text-xs text-slate-400 text-center py-4">Aucune notification récente.</p>
                @else
                    <div class="space-y-2.5">
                        @foreach($notifications as $notif)
                            <div class="p-3 rounded-xl text-xs {{ $notif->lu ? 'bg-slate-50 text-slate-600' : 'bg-brand-50/70 border border-brand-100 text-slate-900' }}">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-bold text-[11px] {{ $notif->lu ? 'text-slate-700' : 'text-brand-900' }}">{{ $notif->titre }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-[11px] mt-1 text-slate-500 leading-relaxed">{{ $notif->message }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
