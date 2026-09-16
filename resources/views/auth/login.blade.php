@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-6" x-data="{ 
    activeTab: '{{ request()->routeIs('register') ? 'register' : 'login' }}',
    showPassword: false,
    showRegPassword: false,
    showPasswordConfirm: false
}">
    
    <!-- Conteneur Carte d'Authentification Haut de Gamme -->
    <div class="bg-white rounded-[28px] p-6 sm:p-10 border border-slate-200/80 shadow-2xl shadow-slate-900/5 space-y-7 relative overflow-hidden">
        
        <!-- Lueur d'accentuation en arrière-plan -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-gradient-to-br from-brand-500/10 to-medical-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-gradient-to-tr from-brand-500/10 to-purple-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <!-- En-tête avec Logo Officiel de l'Établissement (Détouré & Haute Définition) -->
        <div class="text-center space-y-3 relative z-10">
            <div class="inline-flex items-center justify-center mx-auto transition-transform hover:scale-105 duration-300">
                <img src="{{ asset('images/logo-icon.svg') }}" alt="Hôpital RDV" class="w-16 h-16 drop-shadow-sm">
            </div>
            <div>
                <h1 class="font-heading font-extrabold text-2xl tracking-tight text-slate-900">
                    Hôpital <span class="text-brand-600">RDV</span>
                </h1>
                <p class="text-[11px] uppercase font-bold tracking-widest text-slate-400 mt-0.5">Santé Connectée • Système Hospitalier</p>
            </div>
        </div>

        <!-- Commutateur d'Onglets Fluide (Tabs Switcher avec Transition Douce) -->
        <div class="bg-slate-100/90 p-1.5 rounded-2xl flex items-center gap-1 border border-slate-200/60 relative z-10">
            <button type="button" 
                    @click="activeTab = 'login'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                    class="flex-1 py-2.5 px-4 rounded-xl text-xs font-extrabold transition-all duration-300 flex items-center justify-center gap-2"
                    :class="activeTab === 'login' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/50' : 'text-slate-500 hover:text-slate-900'">
                <i data-lucide="lock" class="w-3.5 h-3.5" :class="activeTab === 'login' ? 'text-brand-600' : 'text-slate-400'"></i>
                <span>Connexion</span>
            </button>

            <button type="button" 
                    @click="activeTab = 'register'; $nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                    class="flex-1 py-2.5 px-4 rounded-xl text-xs font-extrabold transition-all duration-300 flex items-center justify-center gap-2"
                    :class="activeTab === 'register' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/50' : 'text-slate-500 hover:text-slate-900'">
                <i data-lucide="user-plus" class="w-3.5 h-3.5" :class="activeTab === 'register' ? 'text-brand-600' : 'text-slate-400'"></i>
                <span>Créer un Compte</span>
            </button>
        </div>

        <!-- Affichage des erreurs globales -->
        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50/90 border border-rose-200 text-rose-800 text-xs space-y-1 shadow-xs animate-shake">
                <div class="font-bold flex items-center gap-1.5 text-rose-900">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    <span>Veuillez corriger les points suivants :</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 text-rose-700 pl-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ========================================== -->
        <!-- 1. FORMULAIRE DE CONNEXION (TAB LOGIN)     -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'login'" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-5">
            
            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">Adresse Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               placeholder="votre.email@exemple.com"
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">Mot de passe</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="key-round" class="w-4 h-4"></i>
                        </div>
                        <input :type="showPassword ? 'text' : 'password'" name="password" required
                               placeholder="••••••••"
                               class="w-full pl-10 pr-11 py-3 rounded-xl border border-slate-300 text-xs font-medium focus:outline-none focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 transition-all">
                        <button type="button" @click="showPassword = !showPassword; $nextTick(() => { if (window.lucide) lucide.createIcons(); })" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i :data-lucide="showPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-xs">Rester connecté</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 active:scale-[0.99] text-white font-bold text-xs shadow-md shadow-slate-900/10 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4 text-brand-400"></i>
                    <span>Se connecter à mon compte</span>
                </button>
            </form>

        </div>

        <!-- ========================================== -->
        <!-- 2. FORMULAIRE D'INSCRIPTION (TAB REGISTER) -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'register'" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-cloak
             class="space-y-5">
            
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 text-slate-700 text-xs leading-relaxed flex items-start gap-2.5">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5"></i>
                <span class="text-[11px]">
                    <strong>Protection des données (Loi n°2013-450) :</strong> Vos coordonnées sont sécurisées et strictement limitées à la planification de vos soins.
                </span>
            </div>

            <form action="{{ route('register') }}" method="POST" class="space-y-3.5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Nom *</label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required placeholder="Ex: Kouamé"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Prénom *</label>
                        <input type="text" name="prenom" value="{{ old('prenom') }}" required placeholder="Ex: Awa"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 focus:outline-none transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Téléphone *</label>
                        <input type="tel" name="telephone" value="{{ old('telephone') }}" required placeholder="+225 07..."
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Sexe</label>
                        <select name="sexe" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 focus:outline-none transition bg-white">
                            <option value="">Sélectionner</option>
                            <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                            <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Date de naissance</label>
                        <input type="date" name="date_naissance" value="{{ old('date_naissance') }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Contact d'urgence</label>
                        <input type="text" name="contact_urgence" value="{{ old('contact_urgence') }}" placeholder="Ex: Frère (+225...)"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 focus:outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Adresse Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="votre.email@exemple.com"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 focus:outline-none transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Mot de passe *</label>
                        <div class="relative">
                            <input :type="showRegPassword ? 'text' : 'password'" name="password" required placeholder="Min. 6 caractères"
                                   class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 focus:outline-none transition">
                            <button type="button" @click="showRegPassword = !showRegPassword; $nextTick(() => { if (window.lucide) lucide.createIcons(); })" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition"
                                    title="Afficher/Masquer le mot de passe">
                                <i :data-lucide="showRegPassword ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider mb-1">Confirmer *</label>
                        <div class="relative">
                            <input :type="showPasswordConfirm ? 'text' : 'password'" name="password_confirmation" required placeholder="Répéter mot de passe"
                                   class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-4 focus:ring-brand-500/10 focus:border-brand-500 focus:outline-none transition">
                            <button type="button" @click="showPasswordConfirm = !showPasswordConfirm; $nextTick(() => { if (window.lucide) lucide.createIcons(); })" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition"
                                    title="Afficher/Masquer la confirmation">
                                <i :data-lucide="showPasswordConfirm ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 active:scale-[0.99] text-white font-bold text-xs shadow-md shadow-slate-900/10 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-emerald-400"></i>
                    <span>Créer mon compte patient</span>
                </button>
            </form>
        </div>

        <!-- Pied de carte : Badge de Conformité -->
        <div class="text-center pt-2">
            <div class="inline-flex items-center gap-1.5 text-[10px] text-slate-400 bg-slate-50 px-3 py-1 rounded-full border border-slate-100">
                <i data-lucide="shield-check" class="w-3 h-3 text-emerald-600"></i>
                <span>Plateforme certifiée • Conformité Loi n°2013-450</span>
            </div>
        </div>

    </div>
</div>
@endsection
