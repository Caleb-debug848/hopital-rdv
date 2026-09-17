@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ addModal: false, editModal: false, activeCabinet: {} }">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="building-2" class="w-5 h-5 text-brand-600"></i>
                Gestion des Services & Cabinets Médicaux
            </h1>
            <p class="text-xs text-slate-500">Inventaire des bâtiments, étages, salles de consultation et affectation des praticiens</p>
        </div>

        <button @click="addModal = true"
                class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Ajouter une Salle / Cabinet
        </button>
    </div>

    <!-- Synthèse & Capacité Globale -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="door-open" class="w-4 h-4 text-brand-600"></i>
                Total Salles & Cabinets
            </div>
            <div class="text-3xl font-heading font-extrabold text-slate-900">{{ $cabinets->count() }}</div>
            <div class="text-[11px] text-slate-500">Salles de consultation enregistrées</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="layers" class="w-4 h-4 text-brand-600"></i>
                Bâtiments Distincts
            </div>
            <div class="text-3xl font-heading font-extrabold text-brand-700">{{ $batiments->count() }}</div>
            <div class="text-[11px] text-slate-500">Pôles et ailes hospitalières</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-slate-400 text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="users" class="w-4 h-4 text-emerald-600"></i>
                Capacité d'Accueil
            </div>
            <div class="text-3xl font-heading font-extrabold text-emerald-600">{{ $capaciteTotale }}</div>
            <div class="text-[11px] text-slate-500">Praticiens simultanés en poste</div>
        </div>
    </div>

    <!-- Grille des Cabinets -->
    @if($cabinets->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center shadow-card space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 mx-auto flex items-center justify-center">
                <i data-lucide="building-2" class="w-6 h-6"></i>
            </div>
            <div class="text-sm font-bold text-slate-700">Aucun cabinet médical enregistré</div>
            <p class="text-xs text-slate-400 max-w-sm mx-auto">Commencez par ajouter les salles physiques (Bâtiments, Étages, Numéros) pour les assigner aux praticiens.</p>
            <button @click="addModal = true" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition">
                Créer la Première Salle
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($cabinets as $cab)
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-card space-y-4 hover:border-brand-300 transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase px-2 py-0.5 rounded-md bg-slate-100 text-slate-600">
                                <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                {{ $cab->batiment }} — {{ $cab->etage }}
                            </span>
                            <h3 class="text-base font-heading font-bold text-slate-900 mt-1.5">{{ $cab->nom }}</h3>
                        </div>

                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $cab->is_actif ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                            {{ $cab->is_actif ? 'Opérationnel' : 'Inactif' }}
                        </span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Capacité d'accueil :</span>
                            <strong class="text-slate-800 font-mono">{{ $cab->capacite }} praticien(s)</strong>
                        </div>

                        @if($cab->equipements)
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-[11px] text-slate-600 space-y-0.5">
                                <span class="font-bold text-slate-400 text-[10px] uppercase block">Équipements</span>
                                {{ $cab->equipements }}
                            </div>
                        @endif

                        <div class="pt-2 border-t border-slate-100">
                            <span class="font-bold text-slate-400 text-[10px] uppercase block mb-1">Médecins Affectés</span>
                            @forelse($cab->medecins as $med)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-brand-50 text-brand-700 text-[11px] font-bold mr-1 mb-1">
                                    <i data-lucide="stethoscope" class="w-3 h-3"></i>
                                    Dr. {{ $med->nom_complet }}
                                </span>
                            @empty
                                <span class="text-[11px] text-slate-400 italic">Aucun médecin affecté actuellement</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-3 border-t border-slate-100">
                        <button @click="activeCabinet = {{ json_encode($cab) }}; editModal = true"
                                class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1">
                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            Modifier
                        </button>

                        <form action="{{ route('admin.cabinets.destroy', $cab->id) }}" method="POST"
                              onsubmit="return confirm('Êtes-vous certain de vouloir supprimer cette salle ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- MODALE : AJOUTER UN CABINET -->
    <div x-show="addModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4" style="display: none;">
        <div @click.away="addModal = false" class="bg-white rounded-3xl max-w-lg w-full p-4 sm:p-6 space-y-5 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-heading font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="door-open" class="w-4 h-4 text-brand-600"></i>
                    Créer un Cabinet / Salle de Consultation
                </h3>
                <button @click="addModal = false" class="text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('admin.cabinets.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nom / Numéro de Salle *</label>
                    <input type="text" name="nom" required placeholder="ex: Cabinet 104 ou Salle Cardiologie"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Bâtiment / Pôle *</label>
                        <input type="text" name="batiment" required placeholder="ex: Bâtiment 3 - Consultations"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Étage *</label>
                        <input type="text" name="etage" required placeholder="ex: 1er Étage"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Capacité Praticiens *</label>
                    <input type="number" name="capacite" value="1" min="1" max="10" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Équipements & Spécificités</label>
                    <input type="text" name="equipements" placeholder="ex: Table d'examen, ECG, Tensiomètre mural"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">
                        Annuler
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold transition flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Enregistrer la Salle
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODALE : MODIFIER UN CABINET -->
    <div x-show="editModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4" style="display: none;">
        <div @click.away="editModal = false" class="bg-white rounded-3xl max-w-lg w-full p-4 sm:p-6 space-y-5 shadow-2xl border border-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-heading font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4 text-brand-600"></i>
                    Modifier le Cabinet
                </h3>
                <button @click="editModal = false" class="text-slate-400 hover:text-slate-700">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'/admin/cabinets/' + activeCabinet.id" method="POST" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nom / Numéro de Salle *</label>
                    <input type="text" name="nom" x-model="activeCabinet.nom" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Bâtiment / Pôle *</label>
                        <input type="text" name="batiment" x-model="activeCabinet.batiment" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Étage *</label>
                        <input type="text" name="etage" x-model="activeCabinet.etage" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Capacité Praticiens *</label>
                        <input type="number" name="capacite" x-model="activeCabinet.capacite" min="1" max="10" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Statut *</label>
                        <select name="is_actif" x-model="activeCabinet.is_actif"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            <option :value="1">Opérationnel / Actif</option>
                            <option :value="0">En maintenance / Inactif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Équipements & Spécificités</label>
                    <input type="text" name="equipements" x-model="activeCabinet.equipements"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="editModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">
                        Annuler
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold transition flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
