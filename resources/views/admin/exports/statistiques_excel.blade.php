<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <style>
        body, table, td, th {
            font-family: 'Segoe UI', Calibri, Arial, sans-serif;
            font-size: 10pt;
            color: #1e293b;
        }
        .header-main {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            height: 45px;
        }
        .header-sub {
            background-color: #1e293b;
            color: #38bdf8;
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            height: 25px;
        }
        .meta-row {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 9pt;
            text-align: center;
            height: 22px;
            border-bottom: 2px solid #cbd5e1;
        }
        .section-title {
            background-color: #0284c7;
            color: #ffffff;
            font-size: 11pt;
            font-weight: bold;
            padding: 8px 10px;
            height: 30px;
            vertical-align: middle;
        }
        .kpi-th {
            background-color: #334155;
            color: #ffffff;
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
            padding: 6px;
            border: 1px solid #cbd5e1;
        }
        .kpi-td {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
        }
        .th-col {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 9.5pt;
            padding: 8px;
            text-align: left;
            border: 1px solid #0f172a;
            vertical-align: middle;
        }
        .th-col-center {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 9.5pt;
            padding: 8px;
            text-align: center;
            border: 1px solid #0f172a;
            vertical-align: middle;
        }
        .td-data {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 9.5pt;
        }
        .td-center {
            text-align: center;
        }
        .td-right {
            text-align: right;
        }
        .row-even {
            background-color: #ffffff;
        }
        .row-odd {
            background-color: #f8fafc;
        }
        /* Badges de statuts médicaux */
        .badge-termine {
            background-color: #dcfce7;
            color: #15803d;
            font-weight: bold;
            text-align: center;
        }
        .badge-arrive {
            background-color: #e0e7ff;
            color: #4338ca;
            font-weight: bold;
            text-align: center;
        }
        .badge-confirme {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: bold;
            text-align: center;
        }
        .badge-en_attente {
            background-color: #fef9c3;
            color: #854d0e;
            font-weight: bold;
            text-align: center;
        }
        .badge-absent {
            background-color: #fee2e2;
            color: #b91c1c;
            font-weight: bold;
            text-align: center;
        }
        .badge-annule {
            background-color: #f1f5f9;
            color: #64748b;
            font-weight: bold;
            text-align: center;
        }
        .badge-pointe {
            background-color: #ffedd5;
            color: #c2410c;
            font-weight: bold;
            text-align: center;
        }
        .footer-text {
            color: #64748b;
            font-size: 8.5pt;
            font-style: italic;
            text-align: center;
            padding: 12px;
        }
    </style>
</head>
<body>

    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <!-- EN-TÊTE INSTITUTIONNEL -->
        <tr>
            <td colspan="12" class="header-main">
                {{ strtoupper($parametres->nom_hopital ?? 'ÉTABLISSEMENT HOSPITALIER') }}
            </td>
        </tr>
        <tr>
            <td colspan="12" class="header-sub">
                BILAN OFFICIEL D'ACTIVITÉ MÉDICALE & PILOTAGE HOSPITALIER
            </td>
        </tr>
        <tr>
            <td colspan="12" class="meta-row">
                Période analysée : <strong>{{ $periodeLabel }}</strong> &nbsp;|&nbsp; 
                Date d'extraction : {{ now()->timezone('Africa/Douala')->format('d/m/Y à H:i:s') }} (Heure locale Douala) &nbsp;|&nbsp; 
                Contact : {{ $parametres->telephone ?? 'Standard' }} — {{ $parametres->email_contact ?? '' }}
            </td>
        </tr>
        <tr><td colspan="12" style="height: 15px;"></td></tr>

        <!-- SECTION 1 : INDICATEURS CLÉS & SYNTHÈSE DE LA DIRECTION -->
        <tr>
            <td colspan="12" class="section-title">
                1. SYNTHÈSE EXÉCUTIVE & INDICATEURS CLÉS (DIRECTION GÉNÉRALE)
            </td>
        </tr>
        <tr>
            <td colspan="2" class="kpi-th">TOTAL RDV PLANIFIÉS</td>
            <td colspan="2" class="kpi-th">CONSULTATIONS TERMINÉES</td>
            <td colspan="2" class="kpi-th">PATIENTS PRÉSENTS (GUICHET)</td>
            <td colspan="2" class="kpi-th">TAUX D'ASSIDUITÉ (PRÉSENCE)</td>
            <td colspan="2" class="kpi-th">ABSENCES CONSTATÉES</td>
            <td colspan="2" class="kpi-th">TAUX D'ABSENTÉISME</td>
        </tr>
        <tr>
            <td colspan="2" class="kpi-td" style="color: #0284c7;">{{ $totalRdv }}</td>
            <td colspan="2" class="kpi-td" style="color: #15803d;">{{ $statutsBreakdown['termine'] }}</td>
            <td colspan="2" class="kpi-td" style="color: #4338ca;">{{ $statutsBreakdown['arrive'] }}</td>
            <td colspan="2" class="kpi-td" style="color: #059669;">{{ $tauxPresence }}%</td>
            <td colspan="2" class="kpi-td" style="color: #b91c1c;">{{ $statutsBreakdown['absent'] }}</td>
            <td colspan="2" class="kpi-td" style="color: #dc2626;">{{ $tauxAbsenteisme }}%</td>
        </tr>
        <tr><td colspan="12" style="height: 15px;"></td></tr>

        <!-- SECTION 2 : ANALYSE D'AFFLUENCE & HEURES DE POINTE -->
        <tr>
            <td colspan="12" class="section-title">
                2. ANALYSE D'AFFLUENCE & HEURES DE POINTE (RÉPARTITION DU PERSONNEL)
            </td>
        </tr>
        <tr>
            <td colspan="3" class="th-col">Créneau Horaire</td>
            <td colspan="3" class="th-col-center">Volume de Consultations</td>
            <td colspan="3" class="th-col-center">Part du Flux Total (%)</td>
            <td colspan="3" class="th-col-center">Statut d'Affluence</td>
        </tr>
        @php
            $maxFlux = count($creneauxHoraires) > 0 ? max($creneauxHoraires) : 0;
        @endphp
        @foreach($creneauxHoraires as $creneau => $nb)
            @php
                $pct = $totalRdv > 0 ? round(($nb / $totalRdv) * 100, 1) : 0;
                $isPointe = $maxFlux > 0 && $nb === $maxFlux;
            @endphp
            <tr class="{{ $loop->even ? 'row-even' : 'row-odd' }}">
                <td colspan="3" class="td-data"><strong>{{ $creneau }}</strong></td>
                <td colspan="3" class="td-data td-center">{{ $nb }} consultations</td>
                <td colspan="3" class="td-data td-center">{{ $pct }}%</td>
                <td colspan="3" class="td-data td-center {{ $isPointe ? 'badge-pointe' : '' }}">
                    {{ $isPointe ? 'HEURE DE POINTE' : ($nb > 0 ? 'Affluence normale' : 'Calme') }}
                </td>
            </tr>
        @endforeach
        <tr><td colspan="12" style="height: 15px;"></td></tr>

        <!-- SECTION 3 : RÉPARTITION PAR SPÉCIALITÉ -->
        <tr>
            <td colspan="6" class="section-title">
                3. ACTIVITÉ PAR SPÉCIALITÉ MÉDICALE
            </td>
            <td colspan="6" class="section-title">
                4. ACTIVITÉ PAR MÉDECIN PRATICIEN
            </td>
        </tr>
        <tr>
            <!-- Colonnes Spécialités -->
            <td colspan="3" class="th-col">Spécialité</td>
            <td colspan="2" class="th-col-center">Nombre d'Actes</td>
            <td colspan="1" class="th-col-center">Part (%)</td>
            <!-- Colonnes Médecins -->
            <td colspan="2" class="th-col">Praticien</td>
            <td colspan="2" class="th-col">Spécialité</td>
            <td colspan="1" class="th-col">Cabinet</td>
            <td colspan="1" class="th-col-center">RDV</td>
        </tr>
        @php
            $maxRows = max(count($bySpecialite), count($byMedecin));
        @endphp
        @for($i = 0; $i < $maxRows; $i++)
            @php
                $spe = $bySpecialite[$i] ?? null;
                $med = $byMedecin[$i] ?? null;
                $spePct = ($spe && $totalRdv > 0) ? round(($spe->rendez_vous_count / $totalRdv) * 100, 1) : 0;
            @endphp
            <tr class="{{ $i % 2 === 0 ? 'row-even' : 'row-odd' }}">
                <!-- Spécialité -->
                <td colspan="3" class="td-data">
                    {{ $spe ? $spe->nom : '—' }}
                </td>
                <td colspan="2" class="td-data td-center">
                    {{ $spe ? $spe->rendez_vous_count : '—' }}
                </td>
                <td colspan="1" class="td-data td-center">
                    {{ $spe ? $spePct . '%' : '—' }}
                </td>
                <!-- Médecin -->
                <td colspan="2" class="td-data">
                    {{ $med ? 'Dr. ' . $med->nom_complet : '—' }}
                </td>
                <td colspan="2" class="td-data">
                    {{ $med ? ($med->specialite?->nom ?? '—') : '—' }}
                </td>
                <td colspan="1" class="td-data">
                    {{ $med ? ($med->cabinet ? $med->cabinet->nom_court : ($med->bureau ?? '—')) : '—' }}
                </td>
                <td colspan="1" class="td-data td-center">
                    {{ $med ? $med->rendez_vous_count : '—' }}
                </td>
            </tr>
        @endfor
        <tr><td colspan="12" style="height: 15px;"></td></tr>

        <!-- SECTION 5 : REGISTRE DÉTAILLÉ DE L'ENSEMBLE DES RDV -->
        <tr>
            <td colspan="12" class="section-title">
                5. REGISTRE DÉTAILLÉ DES CONSULTATIONS (DONNÉES EXHAUSTIVES)
            </td>
        </tr>
        <tr>
            <th class="th-col">Réf. RDV</th>
            <th class="th-col-center">Date</th>
            <th class="th-col-center">Heure</th>
            <th class="th-col">Patient</th>
            <th class="th-col">Téléphone</th>
            <th class="th-col">Email</th>
            <th class="th-col">Praticien</th>
            <th class="th-col">Spécialité</th>
            <th class="th-col">Cabinet / Salle</th>
            <th class="th-col-center">Statut</th>
            <th class="th-col">Motif Médical</th>
            <th class="th-col-center">Réservé le</th>
        </tr>
        @forelse($rdvs as $rdv)
            <tr class="{{ $loop->even ? 'row-even' : 'row-odd' }}">
                <td class="td-data" style="font-weight: bold; font-family: monospace; color: #0369a1;">
                    {{ $rdv->reference_rdv }}
                </td>
                <td class="td-data td-center">
                    {{ $rdv->date_rdv->format('d/m/Y') }}
                </td>
                <td class="td-data td-center" style="font-weight: bold;">
                    {{ substr($rdv->heure_rdv, 0, 5) }}
                </td>
                <td class="td-data" style="font-weight: 600;">
                    {{ $rdv->patient?->user?->full_name ?? 'Inconnu' }}
                </td>
                <td class="td-data">
                    {{ $rdv->patient?->user?->telephone ?? '—' }}
                </td>
                <td class="td-data">
                    {{ $rdv->patient?->user?->email ?? '—' }}
                </td>
                <td class="td-data">
                    {{ $rdv->medecin ? 'Dr. ' . $rdv->medecin->nom_complet : 'Non affecté' }}
                </td>
                <td class="td-data">
                    {{ $rdv->specialite?->nom ?? '—' }}
                </td>
                <td class="td-data">
                    {{ $rdv->medecin?->cabinet ? $rdv->medecin->cabinet->nom_court : ($rdv->medecin?->bureau ?? 'Standard') }}
                </td>
                <td class="td-data td-center badge-{{ $rdv->statut }}">
                    {{ $rdv->statut_badge['label'] }}
                </td>
                <td class="td-data">
                    {{ $rdv->motif ?? 'Non spécifié' }}
                </td>
                <td class="td-data td-center" style="font-size: 8.5pt; color: #64748b;">
                    {{ $rdv->created_at->format('d/m/Y H:i') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12" class="td-data td-center" style="padding: 20px; color: #94a3b8;">
                    Aucun rendez-vous enregistré pour la période sélectionnée.
                </td>
            </tr>
        @endforelse

        <tr><td colspan="12" style="height: 20px;"></td></tr>
        <tr>
            <td colspan="12" class="footer-text">
                Document officiel d'audit et de pilotage hospitalier généré automatiquement depuis le système de gestion de l'établissement {{ $parametres->nom_hopital ?? '' }}.
                Données certifiées conformes aux exigences de traçabilité médico-légale et de secret médical.
            </td>
        </tr>
    </table>

</body>
</html>
