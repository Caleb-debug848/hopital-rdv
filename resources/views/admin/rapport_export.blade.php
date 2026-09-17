<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport d'Activité Médicale & Pilotage — {{ $parametres->nom_hopital ?? 'Hôpital' }}</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
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
                border-radius: 12px !important;
                margin: 0 auto !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 16px 20px !important;
            }
            .page-break {
                page-break-before: always;
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
        <a href="{{ route('admin.statistiques', ['periode' => $periode]) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-xs">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Retour aux Statistiques</span>
        </a>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.statistiques.export', ['periode' => $periode, 'format' => 'excel']) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                <i data-lucide="sheet" class="w-4 h-4"></i>
                <span>Exporter en Excel Pro (.XLS)</span>
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-md">
                <i data-lucide="printer" class="w-4 h-4 text-blue-400"></i>
                <span>Imprimer / Télécharger en PDF</span>
            </button>
        </div>
    </div>

    <!-- DOCUMENT OFFICIEL DE RAPPORT DE DIRECTION (FORMAT A4 HAUTE DÉFINITION) -->
    <div class="print-card w-full max-w-4xl bg-white rounded-3xl border border-slate-200/80 shadow-xl p-6 sm:p-8 space-y-6">

        <!-- En-tête officiel de l'Établissement -->
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-4 border-b-2 border-slate-900">
            <div class="flex items-center gap-3.5">
                @if($parametres->logo_url)
                    <img src="{{ $parametres->logo_url }}" alt="Logo" class="w-14 h-14 object-contain rounded-xl border border-slate-200 p-1">
                @else
                    <div class="w-12 h-12 rounded-xl bg-slate-900 flex items-center justify-center text-white">
                        <i data-lucide="cross" class="w-6 h-6 text-brand-400"></i>
                    </div>
                @endif
                <div>
                    <h1 class="font-heading font-extrabold text-lg text-slate-900 tracking-tight leading-none">
                        {{ $parametres->nom_hopital ?? 'Hôpital RDV' }}
                    </h1>
                    <p class="text-[10px] uppercase font-bold tracking-wider text-slate-500 mt-1">
                        {{ $parametres->slogan ?? 'Direction Médicale & Pilotage Hospitalier' }}
                    </p>
                    <p class="text-[9.5px] text-slate-500 mt-0.5">
                        {{ $parametres->adresse ?? 'Standard Hospitalier' }} &bull; Tél: {{ $parametres->telephone ?? '—' }} &bull; {{ $parametres->email_contact ?? '' }}
                    </p>
                </div>
            </div>

            <div class="bg-slate-900 text-white px-4 py-3 rounded-2xl text-right flex flex-col justify-center sm:min-w-[240px]">
                <div class="text-[9px] uppercase tracking-widest font-bold text-blue-400">Rapport Exécutif d'Activité</div>
                <div class="font-mono text-xs font-bold mt-0.5 tracking-tight">RAP-{{ now()->format('Ymd') }}-{{ strtoupper(substr(md5($periode . now()->toDateString()), 0, 4)) }}</div>
                <div class="text-[9px] text-slate-300 mt-1">
                    Édité le {{ now()->timezone('Africa/Douala')->format('d/m/Y à H:i') }} (GMT+1)
                </div>
                <div class="text-[8.5px] text-slate-400">
                    Par : {{ auth()->user()->full_name ?? 'Direction' }} (Admin)
                </div>
            </div>
        </div>

        <!-- Titre & Période du Bilan -->
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-heading font-extrabold text-slate-900 uppercase tracking-tight">
                    Bilan d'Activité Médicale, Affluence & Absentéisme
                </h2>
                <p class="text-xs text-slate-600 mt-0.5">
                    Période d'évaluation : <strong class="text-slate-900 font-bold">{{ $periodeLabel }}</strong>
                </p>
            </div>
            <div class="text-xs font-bold px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 inline-flex items-center gap-1.5 self-start sm:self-auto">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                <span>Données Officielles Traçables</span>
            </div>
        </div>

        <!-- Section 1 : Indicateurs Clés de Direction (KPIs) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 text-center">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Total Rendez-Vous</span>
                <span class="text-2xl font-heading font-extrabold text-brand-600 mt-1 block">{{ $totalRdv }}</span>
                <span class="text-[9.5px] text-slate-400">Volume global</span>
            </div>
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 text-center">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Taux de Présence</span>
                <span class="text-2xl font-heading font-extrabold text-emerald-600 mt-1 block">{{ $tauxPresence }}%</span>
                <span class="text-[9.5px] text-emerald-600 font-semibold">{{ $statutsBreakdown['termine'] + $statutsBreakdown['arrive'] }} patients honorés</span>
            </div>
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 text-center">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Taux d'Absentéisme</span>
                <span class="text-2xl font-heading font-extrabold text-rose-600 mt-1 block">{{ $tauxAbsenteisme }}%</span>
                <span class="text-[9.5px] text-rose-600 font-semibold">{{ $statutsBreakdown['absent'] }} absences constatées</span>
            </div>
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 text-center">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Consultations Clôturées</span>
                <span class="text-2xl font-heading font-extrabold text-slate-800 mt-1 block">{{ $statutsBreakdown['termine'] }}</span>
                <span class="text-[9.5px] text-slate-400">Prises en charge</span>
            </div>
        </div>

        <!-- Section 2 : Analyse d'Affluence & Heures de Pointe -->
        <div class="space-y-2.5">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-brand-600"></i>
                Distribution des Flux & Heures de Pointe
            </h3>
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-2.5">
                @php
                    $maxFlux = count($creneauxHoraires) > 0 ? max($creneauxHoraires) : 0;
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-2">
                    @foreach($creneauxHoraires as $creneau => $nb)
                        @php
                            $pct = $totalRdv > 0 ? round(($nb / $totalRdv) * 100, 1) : 0;
                            $isPointe = $maxFlux > 0 && $nb === $maxFlux;
                        @endphp
                        <div class="bg-white p-2.5 rounded-xl border {{ $isPointe ? 'border-amber-400 bg-amber-50/40' : 'border-slate-200' }}">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                                <span>{{ $creneau }}</span>
                                @if($isPointe)
                                    <span class="px-1.5 py-0.5 rounded bg-amber-500 text-white text-[8px] uppercase tracking-wider font-extrabold">Pointe</span>
                                @endif
                            </div>
                            <div class="text-base font-extrabold text-slate-900 mt-1">{{ $nb }}</div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                <div class="h-full rounded-full {{ $isPointe ? 'bg-amber-500' : 'bg-brand-600' }}" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="text-[9px] text-slate-500 mt-1 text-right">{{ $pct }}% du flux</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Section 3 : Synthèse par Spécialité et par Médecin -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Spécialités -->
            <div class="space-y-2">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="stethoscope" class="w-4 h-4 text-brand-600"></i>
                    Activité par Pôle Médical
                </h3>
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="p-2.5">Spécialité</th>
                                <th class="p-2.5 text-center">Consultations</th>
                                <th class="p-2.5 text-right">Part</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-[11px]">
                            @forelse($bySpecialite->take(6) as $spe)
                                @php $part = $totalRdv > 0 ? round(($spe->rendez_vous_count / $totalRdv) * 100, 1) : 0; @endphp
                                <tr>
                                    <td class="p-2.5 font-bold text-slate-800">{{ $spe->nom }}</td>
                                    <td class="p-2.5 text-center font-bold text-brand-600">{{ $spe->rendez_vous_count }}</td>
                                    <td class="p-2.5 text-right text-slate-500">{{ $part }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="p-3 text-center text-slate-400">Aucune donnée</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Médecins -->
            <div class="space-y-2">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-brand-600"></i>
                    Charge par Praticien
                </h3>
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="p-2.5">Praticien</th>
                                <th class="p-2.5">Cabinet</th>
                                <th class="p-2.5 text-right">RDV</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-[11px]">
                            @forelse($byMedecin->take(6) as $med)
                                <tr>
                                    <td class="p-2.5">
                                        <div class="font-bold text-slate-800">Dr. {{ $med->nom_complet }}</div>
                                        <div class="text-[9px] text-slate-400">{{ $med->specialite?->nom ?? 'Général' }}</div>
                                    </td>
                                    <td class="p-2.5 text-slate-600 text-[10px]">
                                        {{ $med->cabinet ? $med->cabinet->nom_court : ($med->bureau ?? '—') }}
                                    </td>
                                    <td class="p-2.5 text-right font-extrabold text-slate-800">
                                        {{ $med->rendez_vous_count }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="p-3 text-center text-slate-400">Aucune donnée</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section 4 : Registre Synthétique des Consultations Récentes -->
        <div class="space-y-2">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-brand-600"></i>
                Registre des Consultations (Échantillon de la période)
            </h3>
            <div class="bg-white border border-slate-200 rounded-2xl overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[500px] sm:min-w-0">
                    <thead class="bg-slate-900 text-white text-[9.5px] uppercase tracking-wider">
                        <tr>
                            <th class="p-2.5">Réf.</th>
                            <th class="p-2.5">Date & Heure</th>
                            <th class="p-2.5">Patient</th>
                            <th class="p-2.5">Médecin</th>
                            <th class="p-2.5">Cabinet</th>
                            <th class="p-2.5 text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[10.5px]">
                        @forelse($rdvs->take(10) as $rdv)
                            <tr class="{{ $loop->even ? 'bg-slate-50/60' : 'bg-white' }}">
                                <td class="p-2.5 font-mono font-bold text-slate-700">{{ $rdv->reference_rdv }}</td>
                                <td class="p-2.5">
                                    <span class="font-bold text-slate-800">{{ $rdv->date_rdv->format('d/m/Y') }}</span>
                                    <span class="text-slate-500 font-normal">({{ substr($rdv->heure_rdv, 0, 5) }})</span>
                                </td>
                                <td class="p-2.5 font-semibold text-slate-800">
                                    {{ $rdv->patient?->user?->full_name ?? 'Inconnu' }}
                                </td>
                                <td class="p-2.5 text-slate-700">
                                    {{ $rdv->medecin ? 'Dr. ' . $rdv->medecin->nom_complet : '—' }}
                                </td>
                                <td class="p-2.5 text-slate-600">
                                    {{ $rdv->medecin?->cabinet ? $rdv->medecin->cabinet->nom_court : ($rdv->medecin?->bureau ?? '—') }}
                                </td>
                                <td class="p-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold border {{ $rdv->statut_badge['bg'] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $rdv->statut_badge['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-4 text-center text-slate-400">Aucun rendez-vous sur cette période</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rdvs->count() > 10)
                <p class="text-[9.5px] text-slate-500 text-right italic">
                    Affichage des 10 premières consultations. Pour consulter l'ensemble des {{ $rdvs->count() }} rendez-vous avec tous les détails, téléchargez le classeur Excel Pro.
                </p>
            @endif
        </div>

        <!-- Section 5 : Cadre de Validation & Signatures Officielles -->
        <div class="pt-4 border-t-2 border-slate-200 grid grid-cols-2 sm:grid-cols-3 gap-6 text-center text-xs">
            <div class="space-y-10">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Le Responsable du Système</span>
                <div class="text-[11px] font-bold text-slate-800">{{ auth()->user()->full_name ?? 'Administrateur' }}</div>
            </div>
            <div class="space-y-10">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Visa Direction Médicale</span>
                <div class="text-[10px] text-slate-400 italic">Signature & Date</div>
            </div>
            <div class="col-span-2 sm:col-span-1 space-y-10">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Cachet Officiel Établissement</span>
                <div class="w-20 h-20 border-2 border-dashed border-slate-300 rounded-full mx-auto flex items-center justify-center text-[8px] text-slate-400 font-bold uppercase tracking-widest">
                    Sceau Hospitalier
                </div>
            </div>
        </div>

        <!-- Mentions légales de bas de page -->
        <div class="text-center text-[9px] text-slate-400 pt-2 border-t border-slate-100 space-y-0.5">
            <p>Document officiel d'audit produit par le système d'information de l'hôpital &bull; Conforme aux règles de confidentialité médicale</p>
            <p>Horodatage certifié : {{ now()->timezone('Africa/Douala')->format('d/m/Y H:i:s') }} (Fuseau Africa/Douala)</p>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
