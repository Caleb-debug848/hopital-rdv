@extends('layouts.app')

@section('content')
<div class="space-y-6" 
     x-data="{ 
         addSpecialiteModal: {{ $errors->any() && !old('_method') ? 'true' : 'false' }}, 
         editSpecialiteModal: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }},
         editingSpecialite: { id: '', nom: '', description: '', icone: 'stethoscope' },
         openEdit(spe) {
             this.editingSpecialite = { ...spe };
             this.editSpecialiteModal = true;
         }
     }">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight">Spécialités Médicales</h1>
            <p class="text-xs text-slate-500">Organisation des pôles de consultations et départements de l'hôpital</p>
        </div>
        <button type="button" @click="addSpecialiteModal = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition min-h-[44px]">
            <i data-lucide="plus" class="w-4 h-4 text-brand-400"></i>
            Ajouter une Spécialité
        </button>
    </div>

    <!-- Grille des Spécialités -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        @forelse($specialites as $spe)
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-card hover:border-slate-300 transition flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm">
                            <i data-lucide="{{ $spe->icone ?? 'activity' }}" class="w-5 h-5 text-brand-600"></i>
                        </div>
                        
                        <div class="flex items-center gap-1">
                            <!-- Modifier -->
                            <button type="button" 
                                    @click="openEdit({
                                        id: {{ $spe->id }},
                                        nom: '{{ addslashes($spe->nom) }}',
                                        description: '{{ addslashes($spe->description ?? '') }}',
                                        icone: '{{ addslashes($spe->icone ?? 'stethoscope') }}'
                                    })"
                                    class="p-1 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition" title="Modifier">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            </button>

                            <!-- Supprimer si aucun médecin -->
                            @if($spe->medecins_count === 0)
                                <form action="{{ route('admin.specialites.destroy', $spe->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression de la spécialité {{ addslashes($spe->nom) }} ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Supprimer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div>
                        <h3 class="font-heading font-bold text-sm text-slate-900">{{ $spe->nom }}</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mt-1 line-clamp-2">{{ $spe->description ?? 'Pôle de consultation médicale' }}</p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-slate-500">
                    <span class="inline-flex items-center gap-1">
                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>{{ $spe->medecins_count }} médecin(s)</span>
                    </span>
                    <span class="text-brand-700 font-mono text-[11px] bg-brand-50 px-2 py-0.5 rounded-md border border-brand-200">
                        {{ $spe->rendez_vous_count }} RDV
                    </span>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center space-y-3 bg-white rounded-2xl border border-slate-200">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                    <i data-lucide="activity" class="w-6 h-6"></i>
                </div>
                <div class="font-bold text-sm text-slate-900">Aucune spécialité créée</div>
                <p class="text-xs text-slate-400">Ajoutez votre premier département médical (ex: Cardiologie, Pédiatrie...).</p>
            </div>
        @endforelse
    </div>

    <!-- ============================================== -->
    <!-- MODAL 1 : NOUVELLE SPÉCIALITÉ                  -->
    <!-- ============================================== -->
    <div x-show="addSpecialiteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full space-y-4 shadow-xl border border-slate-200" @click.outside="addSpecialiteModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-heading font-extrabold text-slate-900">Nouvelle Spécialité Médicale</h3>
                    <p class="text-xs text-slate-500">Créer un nouveau pôle de consultation pour l'établissement</p>
                </div>
                <button type="button" @click="addSpecialiteModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.specialites.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nom du pôle médical *</label>
                    <input type="text" name="nom" value="{{ old('nom') }}" required placeholder="Ex: Rhumatologie, Oncologie..."
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
                    @error('nom') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description du service</label>
                    <textarea name="description" rows="3" placeholder="Présentation courte des soins et actes proposés..."
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Icône représentative</label>
                    <select name="icone" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs bg-white font-medium">
                        <option value="heart-pulse">Cœur / Cardiaque (heart-pulse)</option>
                        <option value="stethoscope" selected>Stéthoscope / Général (stethoscope)</option>
                        <option value="sparkles">Gynécologie / Soins (sparkles)</option>
                        <option value="baby">Pédiatrie / Enfance (baby)</option>
                        <option value="eye">Ophtalmologie / Vision (eye)</option>
                        <option value="ear">ORL / Audition (ear)</option>
                        <option value="sun">Dermatologie / Peau (sun)</option>
                        <option value="activity">Neurologie / Activité (activity)</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="addSpecialiteModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                        Annuler
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 2 : MODIFIER UNE SPÉCIALITÉ              -->
    <!-- ============================================== -->
    <div x-show="editSpecialiteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full space-y-4 shadow-xl border border-slate-200" @click.outside="editSpecialiteModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-heading font-extrabold text-slate-900">Modifier la Spécialité</h3>
                    <p class="text-xs text-slate-500">Mettre à jour le nom et la description du service</p>
                </div>
                <button type="button" @click="editSpecialiteModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/specialites') }}/' + editingSpecialite.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nom du pôle médical *</label>
                    <input type="text" name="nom" x-model="editingSpecialite.nom" required
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Description du service</label>
                    <textarea name="description" x-model="editingSpecialite.description" rows="3"
                              class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Icône représentative</label>
                    <select name="icone" x-model="editingSpecialite.icone" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs bg-white font-medium">
                        <option value="heart-pulse">Cœur / Cardiaque (heart-pulse)</option>
                        <option value="stethoscope">Stéthoscope / Général (stethoscope)</option>
                        <option value="sparkles">Gynécologie / Soins (sparkles)</option>
                        <option value="baby">Pédiatrie / Enfance (baby)</option>
                        <option value="eye">Ophtalmologie / Vision (eye)</option>
                        <option value="ear">ORL / Audition (ear)</option>
                        <option value="sun">Dermatologie / Peau (sun)</option>
                        <option value="activity">Neurologie / Activité (activity)</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                    <button type="button" @click="editSpecialiteModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                        Annuler
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-xs transition flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
