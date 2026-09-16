<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Hôpital RDV — Système de Gestion des Consultations' }}</title>
    
    <!-- Favicon Vectoriel SVG Moderne pour Navigateur -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Polices Google Fonts Professionnelles -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        medical: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            200: '#99f6e4',
                            500: '#0d9488',
                            600: '#0f766e',
                            700: '#115e59',
                            800: '#134e4a',
                            900: '#042f2e',
                        },
                        sidebar: {
                            bg: '#0f172a',
                            hover: '#1e293b',
                            active: '#1e293b',
                            border: '#1e293b',
                            text: '#94a3b8',
                            activeText: '#ffffff',
                        }
                    },
                    boxShadow: {
                        'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02)',
                        'card': '0 0 0 1px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.05)',
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Lucide Icons Vectoriels Professionnels -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- NProgress & instant.page pour une navigation ultra rapide et fluide -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/instant.page@5.2.0/instantpage.js" type="module"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Smooth transitions for sidebar toggle */
        .sidebar-transition {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* NProgress Bar personnalisée Cobalt Médical */
        #nprogress .bar {
            background: #2563eb !important;
            height: 3px !important;
        }
        #nprogress .peg {
            box-shadow: 0 0 10px #2563eb, 0 0 5px #2563eb !important;
        }
        #nprogress .spinner { display: none !important; }
    </style>
</head>
<body class="h-full font-sans text-slate-800 antialiased bg-[#f8fafc]" 
      x-data="{ 
          sidebarOpen: false,
          sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true',
          toggleCollapse() {
              this.sidebarCollapsed = !this.sidebarCollapsed;
              localStorage.setItem('sidebar_collapsed', this.sidebarCollapsed);
              this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
          }
      }">

@auth
    <!-- LAYOUT AUTHENTIFIÉ AVEC SIDEBAR PLIABLE / DÉPLIABLE MODERNE -->
    <div class="min-h-full flex">
        
        <!-- Backdrop mobile -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 lg:hidden"
             x-cloak></div>

        <!-- SIDEBAR GAUCHE DARK NAVY AVEC TRANSITION FLUIDE -->
        <aside :class="[
                  sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                  sidebarCollapsed ? 'lg:w-20' : 'lg:w-64'
               ]"
               class="fixed lg:sticky top-0 inset-y-0 left-0 z-50 w-64 bg-sidebar-bg text-sidebar-text flex flex-col justify-between border-r border-slate-800 sidebar-transition h-screen overflow-y-auto overflow-x-hidden select-none">
            
            <div class="p-4 flex flex-col gap-6">
                
                <!-- En-tête Sidebar & Logo & Bouton Rétracter -->
                <div class="flex items-center justify-between min-h-[44px]">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group overflow-hidden">
                        <img src="{{ asset('images/logo-icon.svg') }}" alt="Hôpital RDV" class="w-10 h-10 flex-shrink-0 transition-transform duration-300 group-hover:scale-105">
                        <div class="flex flex-col whitespace-nowrap overflow-hidden transition-all duration-300"
                             :class="sidebarCollapsed ? 'lg:opacity-0 lg:w-0' : 'opacity-100 w-auto'">
                            <span class="font-heading font-extrabold text-base tracking-tight text-white">
                                Hôpital <span class="text-blue-400">RDV</span>
                            </span>
                            <span class="text-[9px] uppercase font-bold tracking-wider text-slate-400">Santé Connectée</span>
                        </div>
                    </a>
                    
                    <!-- Bouton Rétracter Desktop -->
                    <button type="button" 
                            @click="toggleCollapse()" 
                            class="hidden lg:flex p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition"
                            :title="sidebarCollapsed ? 'Déplier la barre latérale' : 'Réduire la barre latérale'">
                        <i data-lucide="panel-left-close" class="w-4 h-4 transition-transform duration-300" :class="sidebarCollapsed ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- Bouton Fermer Mobile -->
                    <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Navigation selon le Rôle -->
                <nav class="flex flex-col gap-1.5">
                    
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-3 py-1 whitespace-nowrap overflow-hidden transition-all duration-200"
                         :class="sidebarCollapsed ? 'lg:opacity-0 lg:h-0 lg:py-0' : 'opacity-100'">
                        Navigation
                    </div>

                    @if(Auth::user()->isPatient())
                        <!-- Espace Patient Links -->
                        <a href="{{ route('patient.dashboard') }}" 
                           :title="sidebarCollapsed ? 'Tableau de bord' : ''"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('patient.dashboard') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-brand-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('patient.dashboard') ? 'text-brand-400' : 'text-slate-400' }}"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Tableau de bord</span>
                        </a>

                        <a href="{{ route('patient.rendez-vous.create') }}" 
                           :title="sidebarCollapsed ? 'Prendre Rendez-vous' : ''"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('patient.rendez-vous.create') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-brand-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                            <i data-lucide="calendar-plus" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('patient.rendez-vous.create') ? 'text-brand-400' : 'text-slate-400' }}"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Prendre RDV</span>
                        </a>

                        <a href="{{ route('patient.rendez-vous.index') }}" 
                           :title="sidebarCollapsed ? 'Mes Rendez-vous' : ''"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('patient.rendez-vous.index*') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-brand-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                            <i data-lucide="calendar-check-2" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('patient.rendez-vous.index*') ? 'text-brand-400' : 'text-slate-400' }}"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Mes Rendez-vous</span>
                        </a>

                    @elseif(Auth::user()->isMedecin())
                        <!-- Espace Médecin Links -->
                        <a href="{{ route('medecin.dashboard') }}" 
                           :title="sidebarCollapsed ? 'Consultations du jour' : ''"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('medecin.dashboard') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-brand-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                            <i data-lucide="activity" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('medecin.dashboard') ? 'text-brand-400' : 'text-slate-400' }}"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Consultations du jour</span>
                        </a>

                        <a href="{{ route('medecin.planning') }}" 
                           :title="sidebarCollapsed ? 'Planning & Horaires' : ''"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('medecin.planning') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-brand-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                            <i data-lucide="calendar" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('medecin.planning') ? 'text-brand-400' : 'text-slate-400' }}"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Planning & Horaires</span>
                        </a>

                    @elseif(Auth::user()->role === 'secretaire')
                        <!-- Espace Secrétaire Links -->
                        <a href="{{ route('secretaire.guichet') }}" 
                           :title="sidebarCollapsed ? 'Guichet d\'Accueil' : ''"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('secretaire.guichet') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-amber-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                            <i data-lucide="clipboard-list" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('secretaire.guichet') ? 'text-amber-400' : 'text-slate-400' }}"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Guichet d'Accueil</span>
                        </a>

                    @elseif(Auth::user()->isAdmin())
                        <!-- Espace Admin Links -->
                        <a href="{{ route('admin.dashboard') }}" 
                           :title="sidebarCollapsed ? 'Vue Générale' : ''"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-brand-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                            <i data-lucide="layout-grid" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-brand-400' : 'text-slate-400' }}"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Vue Générale</span>
                        </a>

                        <a href="{{ route('admin.statistiques') }}" 
                           :title="sidebarCollapsed ? 'Statistiques' : ''"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.statistiques') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-brand-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('admin.statistiques') ? 'text-brand-400' : 'text-slate-400' }}"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Statistiques</span>
                        </a>

                        <a href="{{ route('admin.users.index') }}" 
                           :title="sidebarCollapsed ? 'Gestion des Comptes' : ''"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.users*') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-brand-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                            <i data-lucide="users" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('admin.users*') ? 'text-brand-400' : 'text-slate-400' }}"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Comptes & Rôles</span>
                        </a>

                        <a href="{{ route('admin.medecins.index') }}" 
                           :title="sidebarCollapsed ? 'Praticiens' : ''"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.medecins*') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-brand-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                            <i data-lucide="stethoscope" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('admin.medecins*') ? 'text-brand-400' : 'text-slate-400' }}"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Praticiens</span>
                        </a>

                        <a href="{{ route('admin.specialites.index') }}" 
                           :title="sidebarCollapsed ? 'Spécialités' : ''"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.specialites*') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-brand-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                            <i data-lucide="layers" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('admin.specialites*') ? 'text-brand-400' : 'text-slate-400' }}"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Spécialités</span>
                        </a>

                        <a href="{{ route('secretaire.guichet') }}" 
                           :title="sidebarCollapsed ? 'Vue Guichet Direct' : ''"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('secretaire.guichet') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-amber-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                            <i data-lucide="clipboard-list" class="w-4 h-4 flex-shrink-0 text-slate-400"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Guichet Direct</span>
                        </a>
                    @endif

                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-3 py-1 mt-2 whitespace-nowrap overflow-hidden transition-all duration-200"
                         :class="sidebarCollapsed ? 'lg:opacity-0 lg:h-0 lg:py-0' : 'opacity-100'">
                        Système
                    </div>

                    <a href="{{ route('notifications.index') }}" 
                       :title="sidebarCollapsed ? 'Notifications' : ''"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition {{ request()->routeIs('notifications*') ? 'bg-sidebar-active text-white shadow-xs border-l-2 border-brand-500 font-bold' : 'hover:bg-sidebar-hover hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <i data-lucide="bell" class="w-4 h-4 flex-shrink-0 {{ request()->routeIs('notifications*') ? 'text-brand-400' : 'text-slate-400' }}"></i>
                            <span class="whitespace-nowrap overflow-hidden transition-all duration-200" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">Notifications</span>
                        </div>
                        @php $unread = Auth::user()->unreadNotificationsCount(); @endphp
                        @if($unread > 0)
                            <span class="px-2 py-0.5 rounded-full bg-brand-600 text-white text-[10px] font-bold"
                                  :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">
                                {{ $unread }}
                            </span>
                        @endif
                    </a>
                </nav>
            </div>

            <!-- Pied de Sidebar : Carte Profil Utilisateur -->
            <div class="p-3 border-t border-slate-800 bg-slate-950/40">
                <div class="flex items-center justify-between gap-2 overflow-hidden">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-slate-800 text-brand-400 border border-slate-700 flex items-center justify-center font-bold text-xs flex-shrink-0">
                            {{ substr(Auth::user()->prenom, 0, 1) }}{{ substr(Auth::user()->nom, 0, 1) }}
                        </div>
                        <div class="flex flex-col min-w-0 whitespace-nowrap overflow-hidden transition-all duration-200"
                             :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">
                            <span class="text-xs font-bold text-white truncate">{{ Auth::user()->full_name }}</span>
                            <span class="text-[10px] text-slate-400 capitalize truncate">
                                @if(Auth::user()->role === 'admin') Administrateur
                                @elseif(Auth::user()->role === 'medecin') Praticien
                                @elseif(Auth::user()->role === 'secretaire') Secrétariat
                                @else Patient
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    <form action="{{ route('logout') }}" method="POST" :class="sidebarCollapsed ? 'lg:hidden' : 'inline'">
                        @csrf
                        <button type="submit" title="Se déconnecter" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- ZONE DE CONTENU PRINCIPALE AVEC EXPANSION FLUIDE -->
        <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
            
            <!-- Topbar Header -->
            <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 px-4 sm:px-8 py-3.5 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <!-- Toggle Mobile -->
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    
                    <!-- Toggle Desktop Rapide depuis la Topbar -->
                    <button @click="toggleCollapse()" 
                            class="hidden lg:flex p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition"
                            :title="sidebarCollapsed ? 'Déplier la barre latérale' : 'Réduire la barre latérale'">
                        <i data-lucide="panel-left" class="w-4 h-4"></i>
                    </button>

                    <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-slate-500">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                        <span>Établissement Hospitalier • Système Centralisé</span>
                    </div>
                </div>

                <!-- Barre d'outils droite -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('notifications.index') }}" class="relative p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        @if($unread > 0)
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-rose-600 ring-2 ring-white"></span>
                        @endif
                    </a>

                    @if(Auth::user()->isPatient())
                        <a href="{{ route('patient.rendez-vous.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-xs transition">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Nouveau RDV</span>
                        </a>
                    @endif
                </div>
            </header>

            <!-- Alertes Flash -->
            <div class="px-4 sm:px-8 pt-4 w-full">
                @if(session('success'))
                    <div x-data="{ show: true }" 
                         x-init="setTimeout(() => show = false, 7000)" 
                         x-show="show" 
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="flex items-center justify-between p-3.5 mb-3 text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200 shadow-xs text-xs font-medium" 
                         role="alert">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-800 p-1 rounded">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" 
                         x-init="setTimeout(() => show = false, 8000)" 
                         x-show="show" 
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="flex items-center justify-between p-3.5 mb-3 text-rose-800 rounded-xl bg-rose-50 border border-rose-200 shadow-xs text-xs font-medium" 
                         role="alert">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 flex-shrink-0"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-800 p-1 rounded">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                @endif

                @if(session('info'))
                    <div x-data="{ show: true }" 
                         x-init="setTimeout(() => show = false, 7000)" 
                         x-show="show" 
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="flex items-center justify-between p-3.5 mb-3 text-sky-800 rounded-xl bg-sky-50 border border-sky-200 shadow-xs text-xs font-medium" 
                         role="alert">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="info" class="w-4 h-4 text-sky-600 flex-shrink-0"></i>
                            <span>{{ session('info') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-sky-500 hover:text-sky-800 p-1 rounded">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Contenu de la Vue -->
            <main class="flex-1 p-4 sm:p-8">
                @yield('content')
            </main>

            <!-- Footer sobre -->
            <footer class="border-t border-slate-200/80 bg-white px-4 sm:px-8 py-4 text-xs text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2 mt-auto">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-700">Hôpital RDV</span>
                    <span>&copy; {{ date('Y') }} — Solution de Gestion des Consultations Médicales.</span>
                </div>
                <div class="flex items-center gap-1.5 text-[11px]">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Conformité Loi n°2013-450 (Protection des données personnelles)</span>
                </div>
            </footer>
        </div>
    </div>
@else
    <!-- LAYOUT PUBLIC / VISITEUR -->
    <div class="min-h-full flex flex-col">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-icon.svg') }}" alt="Hôpital RDV" class="w-10 h-10 flex-shrink-0 transition-transform duration-300 hover:scale-105">
                    <div class="flex flex-col">
                        <span class="font-heading font-extrabold text-base tracking-tight text-slate-900">
                            Hôpital <span class="text-brand-600">RDV</span>
                        </span>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Système Médical</span>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="px-3.5 py-2 text-xs font-bold text-slate-700 hover:text-brand-600 transition">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand-600 hover:bg-brand-700 shadow-xs transition">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                        Créer un compte
                    </a>
                </div>
            </div>
        </header>

        <!-- Alertes Flash pour visiteurs -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            @if(session('success'))
                <div class="p-3.5 text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-3.5 text-rose-800 rounded-xl bg-rose-50 border border-rose-200 text-xs font-medium">{{ session('error') }}</div>
            @endif
        </div>

        <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @yield('content')
        </main>

        <footer class="bg-white border-t border-slate-200 py-6 text-xs text-slate-500 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-900">Hôpital RDV</span>
                    <span>&copy; {{ date('Y') }} — Solution de Gestion des Consultations Médicales.</span>
                </div>
                <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Conformité Loi n°2013-450 (Protection des données personnelles)</span>
                </div>
            </div>
        </footer>
    </div>
@endauth

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) { lucide.createIcons(); }
            if (window.NProgress) { NProgress.done(); }
        });
        document.addEventListener('alpine:initialized', () => {
            if (window.lucide) { lucide.createIcons(); }
        });

        // Déclencher NProgress au clic sur les liens internes
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href && link.href.startsWith(window.location.origin) && !link.hasAttribute('download') && link.target !== '_blank' && !link.href.includes('#')) {
                if (window.NProgress) NProgress.start();
            }
        });

        window.addEventListener('pageshow', () => {
            if (window.NProgress) NProgress.done();
        });
    </script>
    @stack('scripts')
</body>
</html>
