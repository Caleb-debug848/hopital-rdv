<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Consultations — {{ $patient->user->full_name }}</title>
    
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
            }
            tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="font-sans text-slate-800 antialiased min-h-full flex flex-col items-center justify-start p-4 sm:p-6 lg:p-8">

    <!-- Barre d'outils supérieure (Masquée à l'impression) -->
    <div class="w-full max-w-4xl mb-5 flex flex-wrap items-center justify-between gap-3 no-print">
        <a href="{{ route('patient.rendez-vous.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-xs">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Retour à mes rendez-vous</span>
        </a>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-md">
                <i data-lucide="printer" class="w-4 h-4 text-blue-400"></i>
                <span>Imprimer / Télécharger le Dossier PDF</span>
            </button>
        </div>
    </div>

    <!-- DOCUMENT HISTORIQUE MÉDICAL (FORMAT A4) -->
    <div class="print-card w-full max-w-4xl bg-white rounded-3xl border border-slate-200/80 shadow-xl p-6 sm:p-10 space-y-5">
        
        <!-- En-tête -->
        <div class="flex items-start justify-between gap-4 pb-4 border-b-2 border-slate-900">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-icon.svg') }}" alt="Logo" class="w-12 h-12 flex-shrink-0">
                <div>
                    <h1 class="font-heading font-extrabold text-xl text-slate-900 tracking-tight leading-none">
                        Hôpital <span class="text-blue-600">RDV</span>
                    </h1>
                    <p class="text-[9.5px] uppercase font-bold tracking-widest text-slate-500 mt-1">Dossier de Suivi & Historique des Consultations</p>
                </div>
            </div>

            <div class="text-right space-y-0.5">
                <div class="text-xs font-mono font-bold text-slate-700">Dossier Patient : #PAT-{{ str_pad($patient->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div class="text-[9.5px] text-slate-400">Édité le {{ now()->format('d/m/Y à H:i') }}</div>
            </div>
        </div>

        <!-- Fiche d'identification Patient -->
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <span class="text-[9.5px] uppercase font-bold text-slate-400 block mb-0.5">Identité du Patient</span>
                <span class="font-bold text-sm text-slate-900">{{ $patient->user->full_name }}</span>
            </div>
            <div>
                <span class="text-[9.5px] uppercase font-bold text-slate-400 block mb-0.5">Contact</span>
                <span class="font-semibold text-slate-700">{{ $patient->user->telephone }}</span>
                <span class="text-slate-500 block text-[11px]">{{ $patient->user->email }}</span>
            </div>
            <div>
                <span class="text-[9.5px] uppercase font-bold text-slate-400 block mb-0.5">Informations de santé</span>
                <span class="text-slate-700">Sexe : {{ $patient->sexe == 'F' ? 'Féminin' : ($patient->sexe == 'M' ? 'Masculin' : 'Non précisé') }}</span>
                @if($patient->date_naissance)
                    <span class="text-slate-500 block text-[11px]">Né(e) le : {{ \Carbon\Carbon::parse($patient->date_naissance)->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>

        <!-- Tableau chronologique des consultations -->
        <div class="space-y-2.5">
            <h2 class="font-heading font-extrabold text-xs uppercase tracking-wider text-slate-900 flex items-center gap-2">
                <i data-lucide="history" class="w-3.5 h-3.5 text-blue-600"></i>
                <span>Relevé chronologique des consultations ({{ $rendezVous->count() }})</span>
            </h2>

            @if($rendezVous->isEmpty())
                <div class="p-6 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-slate-200">
                    Aucune consultation enregistrée à ce jour.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border border-slate-200 rounded-xl overflow-hidden">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[9.5px] font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3.5">Date & Heure</th>
                                <th class="py-2.5 px-3.5">Référence</th>
                                <th class="py-2.5 px-3.5">Praticien / Spécialité</th>
                                <th class="py-2.5 px-3.5">Motif</th>
                                <th class="py-2.5 px-3.5 text-right">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rendezVous as $rdv)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="py-2.5 px-3.5 font-bold text-slate-900">
                                        {{ \Carbon\Carbon::parse($rdv->date_rdv)->format('d/m/Y') }}
                                        <span class="text-[10px] font-normal text-slate-400 block">{{ substr($rdv->heure_rdv, 0, 5) }}</span>
                                    </td>
                                    <td class="py-2.5 px-3.5 font-mono font-semibold text-slate-600 text-[11px]">
                                        {{ $rdv->reference_rdv }}
                                    </td>
                                    <td class="py-2.5 px-3.5">
                                        <div class="font-bold text-slate-800">{{ $rdv->medecin->nom_complet }}</div>
                                        <div class="text-[10.5px] text-blue-600">{{ $rdv->specialite->nom }}</div>
                                    </td>
                                    <td class="py-2.5 px-3.5 text-slate-600 text-[11px]">
                                        {{ $rdv->motif ?? 'Consultation' }}
                                    </td>
                                    <td class="py-2.5 px-3.5 text-right">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9.5px] font-bold {{ $rdv->statut_badge['bg'] }}">
                                            {{ $rdv->statut_badge['label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Pied de page officiel avec Cachet et Signature -->
        <div class="pt-4 border-t border-slate-200 flex items-center justify-between gap-4 text-[10px] text-slate-400">
            <div class="space-y-0.5">
                <div>Document officiel généré par <strong>Hôpital RDV</strong> • Conforme Loi n°2013-450</div>
                <div class="font-mono text-[9px]">Authenticité certifiée • Dossier Patient Sécurisé</div>
            </div>

            <!-- Bloc Cachet & Signature -->
            <div class="relative w-56 h-20 flex items-center justify-end">
                <img src="{{ asset('images/tampon-hopital.svg') }}" 
                     alt="Cachet Hôpital RDV" 
                     class="w-20 h-20 absolute left-0 top-0 opacity-90 rotate-[-5deg] select-none pointer-events-none">
                <img src="{{ asset('images/signature.png') }}" 
                     alt="Signature Officielle" 
                     class="w-32 h-16 absolute right-0 top-1 object-contain opacity-95 rotate-[-3deg] select-none pointer-events-none">
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
