@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="bell-ring" class="w-5 h-5 text-brand-600"></i>
                Supervision des Rappels & Notifications
            </h1>
            <p class="text-xs text-slate-500">Traçabilité des alertes par e-mail (Brevo SMTP), WhatsApp et notifications internes du système</p>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('admin.rappels.trigger') }}" method="POST"
                  onsubmit="return confirm('Déclencher immédiatement l\'envoi de tous les rappels pour les consultations de demain ?');">
                @csrf
                <button type="submit"
                        class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Déclencher l'Envoi Maintenant
                </button>
            </form>
        </div>
    </div>

    <!-- Bannière État du Moteur Automatique (Cron) -->
    <div class="p-4 rounded-2xl bg-slate-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-md">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-xs font-extrabold tracking-wide text-white uppercase flex items-center gap-2">
                    Moteur d'Automatisation Actif
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-500/30 text-emerald-300 font-bold">CRON OPÉRATIONNEL</span>
                </div>
                <div class="text-xs text-slate-400 mt-0.5">
                    Rappels e-mails & alertes WhatsApp programmés quotidiennement à <strong>08h00</strong> (Fuseau Africa/Douala).
                </div>
            </div>
        </div>

        <div class="text-right font-mono text-xs text-slate-300">
            Serveur : <span class="text-emerald-400 font-bold">Synchronisé</span>
        </div>
    </div>

    <!-- Synthèse Chiffrée -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="mail-check" class="w-4 h-4 text-brand-600"></i>
                Rappels Aujourd'hui
            </div>
            <div class="text-3xl font-heading font-extrabold text-slate-900">{{ $rappelsAujourdhui }}</div>
            <div class="text-[11px] text-slate-500">Alertes délivrées ce jour</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-brand-600 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                Rappels ce Mois
            </div>
            <div class="text-3xl font-heading font-extrabold text-brand-700">{{ $rappelsCeMois }}</div>
            <div class="text-[11px] text-slate-500">Volume mensuel cumulé</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-emerald-600 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="clock" class="w-4 h-4"></i>
                RDV Prévus Demain
            </div>
            <div class="text-3xl font-heading font-extrabold text-emerald-600">{{ $rdvsDemain->count() }}</div>
            <div class="text-[11px] text-slate-500">Consultations en file de rappel</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="bell" class="w-4 h-4 text-slate-500"></i>
                Total Notifications
            </div>
            <div class="text-3xl font-heading font-extrabold text-slate-900">{{ $totalNotifications }}</div>
            <div class="text-[11px] text-slate-500">Toutes catégories confondues</div>
        </div>
    </div>

    <!-- RDV de Demain (File de Rappel Active) -->
    @if($rdvsDemain->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-sm font-heading font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="calendar-clock" class="w-4 h-4 text-brand-600"></i>
                    File des Rendez-vous de Demain ({{ Carbon\Carbon::tomorrow()->translatedFormat('l d F Y') }})
                </h2>
                <span class="text-xs text-slate-400 font-mono">{{ $rdvsDemain->count() }} consultation(s)</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                @foreach($rdvsDemain as $rdvDemain)
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-slate-900">{{ $rdvDemain->reference_rdv }}</span>
                            <span class="font-mono font-bold text-brand-700">{{ substr($rdvDemain->heure_rdv, 0, 5) }}</span>
                        </div>
                        <div>
                            <div class="font-bold text-slate-900">{{ $rdvDemain->patient?->user?->full_name }}</div>
                            <div class="text-slate-500 text-[11px]">{{ $rdvDemain->patient?->user?->telephone }} — {{ $rdvDemain->patient?->user?->email }}</div>
                        </div>
                        <div class="text-[11px] text-slate-400 pt-1 border-t border-slate-200/60">
                            Dr. {{ $rdvDemain->medecin?->nom_complet }} ({{ $rdvDemain->specialite?->nom }})
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Journal des Notifications & Rappels Émis -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <h2 class="text-base font-heading font-extrabold text-slate-900 flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-brand-600"></i>
                Historique des Notifications & Alertes Émises
            </h2>

            <form action="{{ route('admin.rappels.index') }}" method="GET" class="flex items-center gap-2 text-xs">
                <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher patient, email..."
                       class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                <button type="submit" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition">
                    Filtrer
                </button>
            </form>
        </div>

        @if($notifications->isEmpty())
            <div class="text-center py-12 space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                    <i data-lucide="inbox" class="w-6 h-6"></i>
                </div>
                <div class="text-sm font-bold text-slate-700">Aucune notification enregistrée</div>
                <p class="text-xs text-slate-400">Les rappels et confirmations apparaîtront ici dès leur émission.</p>
            </div>
        @else
            <!-- Version Mobile (< 640px) -->
            <div class="block sm:hidden space-y-3">
                @foreach($notifications as $notif)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-[10px] font-bold uppercase text-slate-700">
                                {{ $notif->type }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $notif->created_at->format('d/m/Y H:i') }}</span>
                        </div>

                        <div class="text-xs font-bold text-slate-900">{{ $notif->titre }}</div>
                        <div class="text-xs text-slate-600">{{ $notif->message }}</div>

                        <div class="text-[11px] text-slate-500 pt-2 border-t border-slate-200/60 flex items-center justify-between">
                            <span>Destinataire : <strong class="text-slate-800">{{ $notif->user?->full_name }}</strong></span>
                            <span class="text-emerald-700 font-bold">Délivré</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Version Desktop (>= 640px) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">Date & Heure</th>
                            <th class="py-3 px-3">Destinataire</th>
                            <th class="py-3 px-3">Type</th>
                            <th class="py-3 px-3">Titre & Message</th>
                            <th class="py-3 px-3 text-right">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($notifications as $notif)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-3 whitespace-nowrap font-mono text-slate-500">
                                    {{ $notif->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $notif->user?->full_name ?? 'Utilisateur Inconnu' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $notif->user?->email }}</div>
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-brand-50 text-brand-700 border border-brand-200 uppercase">
                                        {{ str_replace('_', ' ', $notif->type) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-700 max-w-md">
                                    <div class="font-bold text-slate-900">{{ $notif->titre }}</div>
                                    <div class="text-slate-500 text-[11px] truncate">{{ $notif->message }}</div>
                                </td>
                                <td class="py-3 px-3 text-right whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-bold text-xs">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        Délivré
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
