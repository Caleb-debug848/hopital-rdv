<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attestation de Rendez-vous — {{ $rendezVous->reference_rdv }}</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }
        @media print {
            html, body {
                background: #ffffff !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                box-shadow: none !important;
                border: 1.5px solid #0f172a !important;
                border-radius: 16px !important;
                margin: 0 auto !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 20px 26px !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="font-sans text-slate-800 antialiased min-h-full flex flex-col items-center justify-start p-4 sm:p-6 lg:p-8">

    <!-- Barre d'outils supérieure (Masquée à l'impression) -->
    <div class="w-full max-w-3xl mb-5 flex flex-wrap items-center justify-between gap-3 no-print">
        <a href="{{ route('patient.rendez-vous.show', $rendezVous->id) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-xs">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Retour au rendez-vous</span>
        </a>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-md">
                <i data-lucide="printer" class="w-4 h-4 text-blue-400"></i>
                <span>Imprimer / Télécharger en 1 Page PDF</span>
            </button>
        </div>
    </div>

    <!-- DOCUMENT OFFICIEL D'ATTESTATION (CALIBRÉ POUR 1 PAGE A4 EXACTE) -->
    <div class="print-card w-full max-w-3xl bg-white rounded-3xl border border-slate-200/80 shadow-xl p-6 sm:p-10 space-y-5 relative overflow-hidden">
        
        <!-- Filigrane discret -->
        <div class="absolute inset-0 flex items-center justify-center opacity-[0.025] pointer-events-none select-none">
            <img src="{{ asset('images/logo-icon.svg') }}" class="w-80 h-80" alt="">
        </div>

        <!-- En-tête officiel de l'Établissement -->
        <div class="flex items-start justify-between gap-4 pb-4 border-b-2 border-slate-900 relative z-10">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-icon.svg') }}" alt="Logo" class="w-12 h-12 flex-shrink-0">
                <div>
                    <h1 class="font-heading font-extrabold text-xl text-slate-900 tracking-tight leading-none">
                        Hôpital <span class="text-blue-600">RDV</span>
                    </h1>
                    <p class="text-[9.5px] uppercase font-bold tracking-widest text-slate-500 mt-1">Direction des Consultations & Soins Médicaux</p>
                    <p class="text-[10px] text-slate-400">Service Centralisé de Prise de Rendez-vous</p>
                </div>
            </div>

            <div class="text-right space-y-0.5">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-emerald-50 border border-emerald-200 text-emerald-800 text-[10px] font-extrabold uppercase">
                    <i data-lucide="check-circle" class="w-3 h-3 text-emerald-600"></i>
                    <span>Convocation Officielle</span>
                </div>
                <div class="text-xs font-mono font-bold text-slate-700">Réf : {{ $rendezVous->reference_rdv }}</div>
                <div class="text-[9.5px] text-slate-500">Réservation enregistrée le {{ $rendezVous->created_at->format('d/m/Y à H:i') }}</div>
                <div class="text-[9px] text-slate-400">Édition / Impression le {{ now()->format('d/m/Y à H:i') }}</div>
            </div>
        </div>

        <!-- Titre du Document -->
        <div class="text-center space-y-0.5 py-1 relative z-10">
            <h2 class="text-base sm:text-lg font-heading font-extrabold uppercase tracking-wide text-slate-900">
                Attestation de Prise de Rendez-vous
            </h2>
            <p class="text-[11px] text-slate-500">Justificatif de rendez-vous et bon d'accès au guichet hospitalier</p>
        </div>

        <!-- Détails Patient & Praticien en Grille -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 relative z-10">
            
            <!-- Bloc Patient -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                <div class="text-[9.5px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Informations Patient</span>
                </div>
                <div class="space-y-0.5 text-xs">
                    <div class="font-bold text-sm text-slate-900">{{ $rendezVous->patient->user->full_name }}</div>
                    <div class="text-slate-600">Téléphone : <span class="font-semibold text-slate-800">{{ $rendezVous->patient->user->telephone }}</span></div>
                    <div class="text-slate-600">Email : <span class="font-semibold text-slate-800">{{ $rendezVous->patient->user->email }}</span></div>
                    @if($rendezVous->patient->date_naissance)
                        <div class="text-slate-600">Date de naissance : {{ \Carbon\Carbon::parse($rendezVous->patient->date_naissance)->format('d/m/Y') }}</div>
                    @endif
                </div>
            </div>

            <!-- Bloc Consultation & Praticien -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                <div class="text-[9.5px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                    <i data-lucide="stethoscope" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Consultation Médicale</span>
                </div>
                <div class="space-y-0.5 text-xs">
                    <div class="font-bold text-sm text-slate-900">{{ $rendezVous->medecin->nom_complet }}</div>
                    <div class="text-slate-600">Pôle : <span class="font-semibold text-blue-600">{{ $rendezVous->specialite->nom }}</span></div>
                    <div class="text-slate-600">Date : <span class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($rendezVous->date_rdv)->translatedFormat('l d F Y') }}</span></div>
                    <div class="text-slate-600">Heure : <span class="font-bold text-slate-900">{{ substr($rendezVous->heure_rdv, 0, 5) }}</span> (Durée : 30 min)</div>
                </div>
            </div>
        </div>

        <!-- Détails de la prise en charge -->
        <div class="p-3.5 rounded-xl border border-slate-200 text-xs space-y-1 relative z-10">
            <div class="flex justify-between items-center text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                <span>Motif de consultation</span>
                <span>Statut : <strong class="text-slate-900 uppercase font-extrabold">{{ $rendezVous->statut_badge['label'] }}</strong></span>
            </div>
            <p class="text-slate-800 font-medium text-xs">{{ $rendezVous->motif ?? 'Consultation médicale générale' }}</p>
        </div>

        <!-- Consignes importantes pour le patient -->
        <div class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200/80 text-amber-950 text-xs space-y-1.5 relative z-10">
            <div class="font-bold flex items-center gap-1.5 text-[11px] text-amber-900">
                <i data-lucide="info" class="w-3.5 h-3.5 text-amber-600"></i>
                <span>Consignes pour votre venue à l'hôpital :</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-[10.5px] text-amber-800">
                <li>Présentez-vous au <strong>Guichet d'accueil / Secrétariat</strong> 15 minutes avant l'heure prévue.</li>
                <li>Munissez-vous de cette attestation (imprimée ou sur smartphone) et d'une pièce d'identité valide.</li>
                <li>En cas d'empêchement, annulez votre consultation au moins 2h à l'avance pour libérer le créneau.</li>
            </ul>
        </div>

        <!-- Signature et Tampon Hospitalier (Intégré dans la même page) -->
        <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-4 text-xs text-slate-500 relative z-10">
            <div class="space-y-0.5">
                <div class="font-mono text-[9px] text-slate-400 uppercase">Code de vérification numérique :</div>
                <div class="font-mono font-bold text-xs text-slate-800 tracking-wider">SEC-{{ strtoupper(substr(md5($rendezVous->id . $rendezVous->reference_rdv), 0, 12)) }}</div>
                <div class="text-[9.5px] text-slate-400">Authentification certifiée • Loi n°2013-450</div>
            </div>

            <!-- Bloc Cachet Officiel d'Établissement & Signature Manuscrite Réaliste -->
            <div class="relative w-64 h-24 flex items-center justify-end">
                <!-- Cachet / Tampon Hospitalier Circulaire Officiel -->
                <img src="{{ asset('images/tampon-hopital.svg') }}" 
                     alt="Cachet Hôpital RDV" 
                     class="w-24 h-24 absolute left-0 top-0 opacity-90 rotate-[-5deg] select-none pointer-events-none">

                <!-- Signature Manuscrite Réelle Authentique -->
                <img src="{{ asset('images/signature.png') }}" 
                     alt="Signature Officielle" 
                     class="w-36 h-20 absolute right-1 top-1 object-contain opacity-95 rotate-[-3deg] select-none pointer-events-none">
                
                <div class="absolute -bottom-1 right-2 text-right">
                    <span class="text-[8px] font-mono font-bold text-slate-400 uppercase tracking-wider block">Cachet & Signature Validée</span>
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) { lucide.createIcons(); }
        });
    </script>
</body>
</html>
