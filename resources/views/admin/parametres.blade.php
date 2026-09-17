@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="settings" class="w-5 h-5 text-brand-600"></i>
                Paramètres Généraux de l'Établissement
            </h1>
            <p class="text-xs text-slate-500">Personnalisez le nom, les coordonnées, les horaires et la durée standard des créneaux sans toucher au code</p>
        </div>
    </div>

    <!-- Formulaire des Paramètres -->
    <form action="{{ route('admin.parametres.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Colonne Gauche : Identité & Coordonnées -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Identité Officielle -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-5">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <i data-lucide="building-2" class="w-4 h-4 text-brand-600"></i>
                        <h2 class="text-sm font-heading font-bold text-slate-900">Identité Officielle de la Structure</h2>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nom Officiel de l'Hôpital / Clinique *</label>
                            <input type="text" name="nom_hopital" value="{{ old('nom_hopital', $parametres->nom_hopital) }}" required
                                   placeholder="ex: Centre Hospitalier Universitaire de Douala"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            @error('nom_hopital') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Slogan ou Mention Institutionnelle</label>
                            <input type="text" name="slogan" value="{{ old('slogan', $parametres->slogan) }}"
                                   placeholder="ex: Excellence médicale, écoute et prise en charge humaine"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            @error('slogan') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Standard Téléphonique *</label>
                                <input type="text" name="telephone" value="{{ old('telephone', $parametres->telephone) }}" required
                                       placeholder="+237 600 00 00 00"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                                @error('telephone') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Email Officiel / Contact *</label>
                                <input type="email" name="email_contact" value="{{ old('email_contact', $parametres->email_contact) }}" required
                                       placeholder="contact@hopital.cm"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                                @error('email_contact') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Adresse Physique Complète *</label>
                            <input type="text" name="adresse" value="{{ old('adresse', $parametres->adresse) }}" required
                                   placeholder="Avenue des Médecins, Quartier Bonanjo, Douala"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
                            @error('adresse') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Organisation & Créneaux Médicaux -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-5">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <i data-lucide="calendar-clock" class="w-4 h-4 text-brand-600"></i>
                        <h2 class="text-sm font-heading font-bold text-slate-900">Paramétrage des Créneaux & Horaires de Consultation</h2>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-2">Durée Standard par Consultation *</label>
                            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                                @foreach([15 => '15 min (Rapide)', 20 => '20 min', 30 => '30 min (Standard)', 45 => '45 min', 60 => '60 min (Spécialisé)'] as $duree => $label)
                                    <label class="relative flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer transition text-center hover:bg-slate-50 {{ old('duree_creneau_defaut', $parametres->duree_creneau_defaut) == $duree ? 'border-brand-600 bg-brand-50/50 text-brand-900 font-bold' : 'border-slate-200 text-slate-700' }}">
                                        <input type="radio" name="duree_creneau_defaut" value="{{ $duree }}"
                                               {{ old('duree_creneau_defaut', $parametres->duree_creneau_defaut) == $duree ? 'checked' : '' }}
                                               class="sr-only">
                                        <span class="font-mono text-sm">{{ $duree }}m</span>
                                        <span class="text-[10px] text-slate-500 mt-0.5">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('duree_creneau_defaut') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Heure d'Ouverture des Consultations *</label>
                                <input type="time" name="heure_ouverture" value="{{ old('heure_ouverture', $parametres->heure_ouverture) }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                                @error('heure_ouverture') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Heure de Clôture des Consultations *</label>
                                <input type="time" name="heure_fermeture" value="{{ old('heure_fermeture', $parametres->heure_fermeture) }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:outline-none">
                                @error('heure_fermeture') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne Droite : Logo & Sécurité -->
            <div class="space-y-6">
                <!-- Logo & Identité Visuelle -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <i data-lucide="image" class="w-4 h-4 text-brand-600"></i>
                        <h2 class="text-sm font-heading font-bold text-slate-900">Logo Officiel</h2>
                    </div>

                    <div class="text-center p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-300 space-y-3">
                        @if($parametres->logo_path)
                            <img src="{{ asset('storage/' . $parametres->logo_path) }}" alt="Logo Hôpital" class="max-h-20 mx-auto rounded-lg shadow-xs">
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-brand-50 border border-brand-200 text-brand-600 mx-auto flex items-center justify-center">
                                <i data-lucide="cross" class="w-8 h-8"></i>
                            </div>
                            <div class="text-xs font-bold text-slate-700">Logo standard actif</div>
                        @endif

                        <div class="text-[11px] text-slate-400">Format PNG, JPG ou SVG (Max 2 Mo)</div>

                        <label class="px-3 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-bold cursor-pointer transition inline-flex items-center gap-1.5 shadow-2xs">
                            <i data-lucide="upload" class="w-3.5 h-3.5 text-brand-600"></i>
                            Changer le logo
                            <input type="file" name="logo" accept="image/*" class="hidden">
                        </label>
                    </div>
                </div>

                <!-- Enregistrement -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-4">
                    <button type="submit"
                            class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Enregistrer les Modifications
                    </button>
                    <p class="text-[11px] text-slate-400 text-center leading-relaxed">
                        Les changements sont immédiatement pris en compte dans les convocations, emails et fiches de consultation.
                    </p>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection
