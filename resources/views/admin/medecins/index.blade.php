@extends('layouts.app')

@section('content')
<div class="space-y-6" 
     x-data="{ 
         addDoctorModal: {{ $errors->any() && !old('_method') ? 'true' : 'false' }}, 
         editDoctorModal: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }}, 
         showAddPass: false,
         showEditPass: false,
         editingDoctor: {
             id: '',
             nom: '',
             prenom: '',
             email: '',
             telephone: '',
             specialite_id: '',
             service: '',
             bureau: '',
             heure_debut: '08:00',
             heure_fin: '16:00',
             jours: ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi']
         },
         openEdit(doc) {
             this.editingDoctor = { ...doc };
             this.editDoctorModal = true;
         }
     }">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight">Corps Médical & Praticiens</h1>
            <p class="text-xs text-slate-500">Gestion des comptes médecins, plannings de consultation et affectations</p>
        </div>
        <button type="button" @click="addDoctorModal = true" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-xs transition min-h-[44px]">
            <i data-lucide="user-plus" class="w-4 h-4 text-brand-400"></i>
            Ajouter un Praticien
        </button>
    </div>

    <!-- Grille des Médecins -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($medecins as $med)
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-card hover:border-slate-300 transition space-y-4 flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="w-11 h-11 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm">
                            <i data-lucide="stethoscope" class="w-5 h-5 text-slate-600"></i>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $med->statut === 'actif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                {{ ucfirst($med->statut) }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h3 class="font-heading font-bold text-sm text-slate-900">{{ $med->nom_complet }}</h3>
                        <p class="text-xs font-semibold text-brand-600">{{ $med->specialite->nom ?? 'Généraliste' }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $med->service ?? 'Service Hospitalier' }} • {{ $med->bureau ?? 'Bureau' }}</p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5 text-slate-600">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span class="font-mono">{{ $med->user->telephone ?? '—' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                            <span class="truncate">{{ $med->user->email ?? '—' }}</span>
                        </div>
                        <div class="text-[11px] text-slate-500 pt-1 flex items-center gap-1">
                            <i data-lucide="calendar" class="w-3 h-3 text-slate-400"></i>
                            <span>Jours : <strong class="text-slate-700">{{ is_array($med->jours_consultation) ? implode(', ', $med->jours_consultation) : 'Sur RDV' }}</strong></span>
                        </div>
                        <div class="text-[11px] text-slate-500 flex items-center gap-1">
                            <i data-lucide="clock" class="w-3 h-3 text-slate-400"></i>
                            <span>Horaires : {{ substr($med->heure_debut_defaut, 0, 5) }} - {{ substr($med->heure_fin_defaut, 0, 5) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs gap-2">
                    <span class="font-bold text-slate-700">{{ $med->rendez_vous_count }} consultation(s)</span>
                    
                    <div class="flex items-center gap-2">
                        <!-- Modifier -->
                        <button type="button" 
                                @click="openEdit({
                                    id: {{ $med->id }},
                                    nom: '{{ addslashes($med->user->nom ?? '') }}',
                                    prenom: '{{ addslashes($med->user->prenom ?? '') }}',
                                    email: '{{ addslashes($med->user->email ?? '') }}',
                                    telephone: '{{ addslashes($med->user->telephone ?? '') }}',
                                    specialite_id: {{ $med->specialite_id }},
                                    service: '{{ addslashes($med->service ?? '') }}',
                                    bureau: '{{ addslashes($med->bureau ?? '') }}',
                                    heure_debut: '{{ substr($med->heure_debut_defaut, 0, 5) }}',
                                    heure_fin: '{{ substr($med->heure_fin_defaut, 0, 5) }}',
                                    jours: @json($med->jours_consultation ?? [])
                                })"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition" title="Modifier le profil">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>

                        <!-- Activer / Désactiver -->
                        <form action="{{ route('admin.medecins.toggle', $med->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-[11px] font-bold px-2 py-1 rounded-lg border {{ $med->statut === 'actif' ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }}">
                                {{ $med->statut === 'actif' ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>

                        <!-- Supprimer -->
                        <form action="{{ route('admin.medecins.destroy', $med->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression définitive du Dr. {{ addslashes($med->nom_complet) }} ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Supprimer">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center space-y-3 bg-white rounded-2xl border border-slate-200">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                    <i data-lucide="stethoscope" class="w-6 h-6"></i>
                </div>
                <div class="font-bold text-sm text-slate-900">Aucun médecin enregistré</div>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">Cliquez sur « Ajouter un Praticien » pour créer le premier profil médical avec son emploi du temps.</p>
                <button type="button" @click="addDoctorModal = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold shadow-xs">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Ajouter maintenant
                </button>
            </div>
        @endforelse
    </div>

    <!-- ============================================== -->
    <!-- MODAL 1 : CRÉATION D'UN NOUVEAU MÉDECIN        -->
    <!-- ============================================== -->
    <div x-show="addDoctorModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-xl w-full space-y-4 shadow-xl border border-slate-200 overflow-y-auto max-h-[90vh]" @click.outside="addDoctorModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-heading font-extrabold text-slate-900">Enregistrer un Nouveau Médecin</h3>
                    <p class="text-xs text-slate-500">Création du compte praticien et configuration de son planning</p>
                </div>
                <button type="button" @click="addDoctorModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.medecins.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nom de famille *</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required placeholder="Ex: Martin" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
                        @error('nom') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Prénom *</label>
                        <input type="text" name="prenom" value="{{ old('prenom') }}" required placeholder="Ex: Alexandre" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
                        @error('prenom') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Email professionnel (Connexion) *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="dr.martin@hopital.com" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
                        @error('email') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Téléphone *</label>
                        <input type="tel" name="telephone" value="{{ old('telephone') }}" required placeholder="+33 6..." class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
                        @error('telephone') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Spécialité médicale *</label>
                        <select name="specialite_id" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
                            <option value="">Sélectionnez une spécialité</option>
                            @foreach($specialites as $spe)
                                <option value="{{ $spe->id }}" {{ old('specialite_id') == $spe->id ? 'selected' : '' }}>{{ $spe->nom }}</option>
                            @endforeach
                        </select>
                        @error('specialite_id') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Bureau / Cabinet</label>
                        <input type="text" name="bureau" value="{{ old('bureau') }}" placeholder="Ex: Bâtiment B, Cabinet 104" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
                    </div>
                </div>

                <!-- Mot de passe obligatoire de création -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Mot de passe de connexion du médecin *</label>
                    <div class="relative">
                        <input :type="showAddPass ? 'text' : 'password'" name="password" required placeholder="Minimum 6 caractères (ex: Medecin2026!)" class="w-full px-3 py-2.5 pr-10 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium">
                        <button type="button" @click="showAddPass = !showAddPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i :data-lucide="showAddPass ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                        </button>
                    </div>
                    @error('password') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                </div>

                <!-- Horaires par défaut -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Heure début consultation *</label>
                        <input type="time" name="heure_debut" value="{{ old('heure_debut', '08:00') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white font-medium">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Heure fin consultation *</label>
                        <input type="time" name="heure_fin" value="{{ old('heure_fin', '16:00') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white font-medium">
                    </div>
                </div>

                <!-- Jours de consultation -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jours de consultation hebdomadaires *</label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 text-xs">
                        @foreach(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'] as $jour)
                            <label class="inline-flex items-center justify-center gap-1.5 p-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white cursor-pointer transition select-none">
                                <input type="checkbox" name="jours_consultation[]" value="{{ $jour }}" 
                                       {{ is_array(old('jours_consultation')) ? (in_array($jour, old('jours_consultation')) ? 'checked' : '') : (in_array($jour, ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi']) ? 'checked' : '') }}
                                       class="rounded text-brand-600 focus:ring-brand-500">
                                <span class="capitalize text-[11px] font-bold text-slate-700">{{ substr($jour, 0, 3) }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('jours_consultation') <span class="text-[10px] text-rose-600 block mt-0.5">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="addDoctorModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                        Annuler
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition flex items-center justify-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Créer le Médecin
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL 2 : MODIFICATION D'UN MÉDECIN            -->
    <!-- ============================================== -->
    <div x-show="editDoctorModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-2xl p-6 sm:p-8 max-w-xl w-full space-y-4 shadow-xl border border-slate-200 overflow-y-auto max-h-[90vh]" @click.outside="editDoctorModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-heading font-extrabold text-slate-900">Modifier la Fiche du Médecin</h3>
                    <p class="text-xs text-slate-500">Mise à jour des coordonnées, spécialité et planning</p>
                </div>
                <button type="button" @click="editDoctorModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'{{ url('admin/medecins') }}/' + editingDoctor.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nom *</label>
                        <input type="text" name="nom" x-model="editingDoctor.nom" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Prénom *</label>
                        <input type="text" name="prenom" x-model="editingDoctor.prenom" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Email *</label>
                        <input type="email" name="email" x-model="editingDoctor.email" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Téléphone *</label>
                        <input type="tel" name="telephone" x-model="editingDoctor.telephone" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Spécialité *</label>
                        <select name="specialite_id" x-model="editingDoctor.specialite_id" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs bg-white font-medium">
                            @foreach($specialites as $spe)
                                <option value="{{ $spe->id }}">{{ $spe->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Bureau / Cabinet</label>
                        <input type="text" name="bureau" x-model="editingDoctor.bureau" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-medium">
                    </div>
                </div>

                <!-- Mot de passe facultatif à la modification -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                    <div class="relative">
                        <input :type="showEditPass ? 'text' : 'password'" name="password" placeholder="Laisser vide pour conserver l'actuel" class="w-full px-3 py-2.5 pr-10 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 font-medium">
                        <button type="button" @click="showEditPass = !showEditPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i :data-lucide="showEditPass ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Heure début *</label>
                        <input type="time" name="heure_debut" x-model="editingDoctor.heure_debut" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white font-medium">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Heure fin *</label>
                        <input type="time" name="heure_fin" x-model="editingDoctor.heure_fin" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs bg-white font-medium">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jours de consultation hebdomadaires *</label>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 text-xs">
                        @foreach(['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'] as $jour)
                            <label class="inline-flex items-center justify-center gap-1.5 p-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-white cursor-pointer transition select-none">
                                <input type="checkbox" name="jours_consultation[]" value="{{ $jour }}" 
                                       :checked="editingDoctor.jours && editingDoctor.jours.map(j => j.toLowerCase()).includes('{{ $jour }}')"
                                       class="rounded text-brand-600 focus:ring-brand-500">
                                <span class="capitalize text-[11px] font-bold text-slate-700">{{ substr($jour, 0, 3) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="editDoctorModal = false" class="w-1/2 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200 transition">
                        Annuler
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-xs transition flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Enregistrer les Modifications
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
