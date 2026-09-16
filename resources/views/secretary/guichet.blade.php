@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ deskModal: false, patientType: 'existing' }">

    <!-- En-tête Guichet Secrétariat -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-card">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center font-heading font-bold text-lg shadow-sm">
                <i data-lucide="clipboard-list" class="w-6 h-6 text-amber-400"></i>
            </div>
            <div>
                <h1 class="text-xl font-heading font-extrabold text-slate-900">Guichet d'Accueil & Secrétariat</h1>
                <p class="text-xs text-slate-500">Pointage des arrivées en temps réel, gestion des flux et accueil des patients</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" @click="deskModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition">
                <i data-lucide="user-plus" class="w-4 h-4 text-amber-400"></i>
                Nouveau RDV Guichet
            </button>
        </div>
    </div>

    <!-- Statistiques Opérationnelles du Guichet -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Jour</div>
            <div class="text-2xl font-heading font-extrabold text-slate-900 mt-0.5">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card text-center">
            <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Confirmés</div>
            <div class="text-2xl font-heading font-extrabold text-emerald-600 mt-0.5">{{ $stats['confirme'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card text-center">
            <div class="text-[10px] font-bold text-brand-600 uppercase tracking-wider">Arrivés</div>
            <div class="text-2xl font-heading font-extrabold text-brand-600 mt-0.5">{{ $stats['arrive'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card text-center">
            <div class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider">Effectués</div>
            <div class="text-2xl font-heading font-extrabold text-indigo-600 mt-0.5">{{ $stats['termine'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card text-center">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Absents</div>
            <div class="text-2xl font-heading font-extrabold text-slate-600 mt-0.5">{{ $stats['absent'] }}</div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card text-center">
            <div class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Annulés</div>
            <div class="text-2xl font-heading font-extrabold text-rose-600 mt-0.5">{{ $stats['annule'] }}</div>
        </div>
    </div>

    <!-- Barre de Filtres -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-card">
        <form action="{{ route('secretaire.guichet') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Date</label>
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                       class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Médecin</label>
                <select name="medecin_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                    <option value="">Tous les praticiens</option>
                    @foreach($medecins as $m)
                        <option value="{{ $m->id }}" {{ $selectedMedecinId == $m->id ? 'selected' : '' }}>{{ $m->nom_complet }} ({{ $m->specialite->nom }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Statut</label>
                <select name="statut" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                    <option value="">Tous les statuts</option>
                    <option value="confirme" {{ $selectedStatut == 'confirme' ? 'selected' : '' }}>Confirmé</option>
                    <option value="arrive" {{ $selectedStatut == 'arrive' ? 'selected' : '' }}>Patient arrivé</option>
                    <option value="en_attente" {{ $selectedStatut == 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="termine" {{ $selectedStatut == 'termine' ? 'selected' : '' }}>Effectué</option>
                    <option value="absent" {{ $selectedStatut == 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="annule" {{ $selectedStatut == 'annule' ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Recherche (Nom, Tél, Réf)</label>
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ex: Kouamé, #RDV-..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <button type="submit" class="px-3 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tableau Opérationnel du Guichet -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-heading font-extrabold text-slate-900">
                Liste Opérationnelle du Guichet ({{ count($rendezVous) }} rendez-vous)
            </h2>
            <div class="text-xs text-slate-500 font-medium">
                {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l d F Y') }}
            </div>
        </div>

        @if($rendezVous->isEmpty())
            <div class="py-12 text-center text-xs text-slate-400">
                Aucun rendez-vous ne correspond aux critères sélectionnés.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">Heure</th>
                            <th class="py-3 px-3">Patient</th>
                            <th class="py-3 px-3">Téléphone</th>
                            <th class="py-3 px-3">Médecin</th>
                            <th class="py-3 px-3">Spécialité</th>
                            <th class="py-3 px-3">Statut Actuel</th>
                            <th class="py-3 px-3 text-right">Actions Guichet Rapides</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($rendezVous as $rdv)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-3 font-bold text-slate-900">
                                    <span class="px-2 py-1 rounded-lg bg-slate-100 font-mono text-[11px]">{{ substr($rdv->heure_rdv, 0, 5) }}</span>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="font-bold text-slate-900">{{ $rdv->patient->user->full_name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $rdv->reference_rdv }}</div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-600 font-mono">
                                    {{ $rdv->patient->user->telephone }}
                                </td>
                                <td class="py-3.5 px-3 font-semibold text-slate-800">
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
                                <td class="py-3.5 px-3 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <!-- Marquer Arrivé -->
                                        @if($rdv->statut !== 'arrive' && $rdv->statut !== 'termine')
                                            <form action="{{ route('secretaire.rdv.status', $rdv->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="statut" value="arrive">
                                                <button type="submit" title="Marquer Arrivé" class="px-2.5 py-1 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-[11px] border border-brand-200 transition flex items-center gap-1">
                                                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                                                    Arrivé
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Marquer Terminé -->
                                        @if($rdv->statut !== 'termine')
                                            <form action="{{ route('secretaire.rdv.status', $rdv->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="statut" value="termine">
                                                <button type="submit" title="Marquer Terminé" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] border border-emerald-200 transition flex items-center gap-1">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                    Effectué
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Marquer Absent -->
                                        @if($rdv->statut !== 'absent' && $rdv->statut !== 'termine')
                                            <form action="{{ route('secretaire.rdv.status', $rdv->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="statut" value="absent">
                                                <button type="submit" title="Marquer Absent" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-[11px] transition">
                                                    Absent
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

    <!-- Modal Prise de RDV au Guichet (Walk-in) -->
    <div x-show="deskModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-lg w-full space-y-4 shadow-xl border border-slate-200" @click.outside="deskModal = false">
            <div class="text-center space-y-1">
                <h3 class="text-base font-heading font-extrabold text-slate-900">Nouveau Rendez-vous Guichet</h3>
                <p class="text-xs text-slate-500">Enregistrer un rendez-vous direct au comptoir d'accueil</p>
            </div>

            <form action="{{ route('secretaire.rdv.create_desk') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Choix type de patient -->
                <div class="flex items-center gap-4 text-xs font-bold">
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="patient_type" value="existing" x-model="patientType" class="text-brand-600 focus:ring-brand-500">
                        Patient déjà enregistré
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="patient_type" value="new" x-model="patientType" class="text-brand-600 focus:ring-brand-500">
                        Nouveau patient (sur place)
                    </label>
                </div>

                <!-- Patient existant -->
                <div x-show="patientType === 'existing'" class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Sélectionner le patient</label>
                    <select name="patient_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                        <option value="">Choisir un patient...</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}">{{ $p->user->full_name }} ({{ $p->user->telephone }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Nouveau patient -->
                <div x-show="patientType === 'new'" class="space-y-3" x-cloak>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nom *</label>
                            <input type="text" name="nom" placeholder="Nom" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Prénom *</label>
                            <input type="text" name="prenom" placeholder="Prénom" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Téléphone *</label>
                        <input type="tel" name="telephone" placeholder="Téléphone" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Spécialité *</label>
                        <select name="specialite_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            @foreach($specialites as $spe)
                                <option value="{{ $spe->id }}">{{ $spe->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Médecin *</label>
                        <select name="medecin_id" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white">
                            @foreach($medecins as $med)
                                <option value="{{ $med->id }}">{{ $med->nom_complet }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Date *</label>
                        <input type="date" name="date_rdv" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Heure *</label>
                        <input type="time" name="heure_rdv" value="09:00" required
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Motif</label>
                    <input type="text" name="motif" placeholder="Motif de consultation"
                           class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs">
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="deskModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                        Annuler
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                        Enregistrer au guichet
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
