@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="shield-alert" class="w-5 h-5 text-brand-600"></i>
                Journal d'Audit & Traçabilité Médico-Légale
            </h1>
            <p class="text-xs text-slate-500">Registre inviolable des actions du personnel : arrivées pointées, modifications de statut, annulations et adresses IP</p>
        </div>
    </div>

    <!-- Synthèse Chiffrée -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="activity" class="w-4 h-4 text-brand-600"></i>
                Événements Aujourd'hui
            </div>
            <div class="text-3xl font-heading font-extrabold text-slate-900">{{ $totalAujourdhui }}</div>
            <div class="text-[11px] text-slate-500">Actions enregistrées depuis 00h00</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-blue-600 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="user-check" class="w-4 h-4"></i>
                Pointages d'Arrivée
            </div>
            <div class="text-3xl font-heading font-extrabold text-blue-600">{{ $totalPointages }}</div>
            <div class="text-[11px] text-slate-500">Enregistrements d'accueil au guichet</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-amber-600 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="calendar-x" class="w-4 h-4"></i>
                Annulations Traitées
            </div>
            <div class="text-3xl font-heading font-extrabold text-amber-600">{{ $totalAnnulations }}</div>
            <div class="text-[11px] text-slate-500">Rendez-vous déprogrammés</div>
        </div>
    </div>

    <!-- Barre de Filtres & Recherche -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card">
        <form action="{{ route('admin.audit_logs.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-5 gap-3 text-xs">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher par utilisateur, IP, référence..."
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <select name="action" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Toutes les actions</option>
                    @foreach($actionsList as $act)
                        <option value="{{ $act }}" {{ $action === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="role" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">Tous les rôles</option>
                    <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Administrateur</option>
                    <option value="secretaire" {{ $role === 'secretaire' ? 'selected' : '' }}>Secrétariat</option>
                    <option value="medecin" {{ $role === 'medecin' ? 'selected' : '' }}>Médecin</option>
                    <option value="patient" {{ $role === 'patient' ? 'selected' : '' }}>Patient</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    Filtrer
                </button>
                @if($search || $action || $role || $date)
                    <a href="{{ route('admin.audit_logs.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition">
                        Effacer
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Liste du Journal d'Audit -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
        
        @if($logs->isEmpty())
            <div class="text-center py-12 space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                    <i data-lucide="shield" class="w-6 h-6"></i>
                </div>
                <div class="text-sm font-bold text-slate-700">Aucun enregistrement d'audit</div>
                <p class="text-xs text-slate-400">Aucune action ne correspond aux critères de recherche sélectionnés.</p>
            </div>
        @else
            <!-- Vue Mobile (< 640px) -->
            <div class="block sm:hidden space-y-3">
                @foreach($logs as $log)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $log->action_badge['bg'] }}">
                                <i data-lucide="{{ $log->action_badge['icon'] }}" class="w-3 h-3"></i>
                                {{ $log->action_badge['label'] }}
                            </span>
                            <span class="text-[11px] text-slate-400 font-mono">
                                {{ $log->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <div class="text-xs text-slate-900 font-bold leading-relaxed">
                            {{ $log->description }}
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-500 pt-2 border-t border-slate-200/60">
                            <span class="font-medium">Par : <strong class="text-slate-800">{{ $log->user_name }}</strong> ({{ ucfirst($log->user_role) }})</span>
                            <span class="font-mono text-slate-400 text-[10px]">{{ $log->ip_address ?? 'IP Locale' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Vue Tableau Desktop (>= 640px) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">Date & Heure Certifiée</th>
                            <th class="py-3 px-3">Utilisateur & Rôle</th>
                            <th class="py-3 px-3">Action</th>
                            <th class="py-3 px-3">Description & Contexte</th>
                            <th class="py-3 px-3 text-right">Adresse IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <div class="font-mono font-bold text-slate-900">{{ $log->created_at->format('d/m/Y') }}</div>
                                    <div class="text-slate-400 font-mono text-[11px]">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $log->user_name }}</div>
                                    <div class="text-slate-400 text-[11px] capitalize">{{ $log->user_role }}</div>
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $log->action_badge['bg'] }}">
                                        <i data-lucide="{{ $log->action_badge['icon'] }}" class="w-3 h-3"></i>
                                        {{ $log->action_badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-slate-700 font-medium">
                                    {{ $log->description }}
                                </td>
                                <td class="py-3.5 px-3 text-right font-mono text-slate-500 whitespace-nowrap">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-4">
                {{ $logs->links() }}
            </div>
        @endif

    </div>

</div>
@endsection
