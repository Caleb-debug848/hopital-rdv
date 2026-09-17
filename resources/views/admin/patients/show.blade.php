@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête avec Fil d'Ariane -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <a href="{{ route('admin.patients.index') }}" class="hover:text-brand-600 transition flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    Registre des Patients
                </a>
                <span>/</span>
                <span class="text-slate-700 font-bold">Dossier {{ $patient->numero_patient }}</span>
            </div>
            <h1 class="text-2xl font-heading font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="folder-heart" class="w-6 h-6 text-brand-600"></i>
                Dossier Patient Unique — {{ $patient->user->full_name }}
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="tel:{{ $patient->user->telephone }}" class="px-3 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="phone" class="w-3.5 h-3.5 text-brand-600"></i>
                Appeler
            </a>
            <a href="mailto:{{ $patient->user->email }}" class="px-3 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="mail" class="w-3.5 h-3.5 text-brand-600"></i>
                Email
            </a>
        </div>
    </div>

    <!-- Fiche d'Identité & Coordonnées -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations Civiles -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-card space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-200 text-brand-700 flex items-center justify-center font-heading font-extrabold text-lg">
                        {{ strtoupper(substr($patient->user->prenom, 0, 1) . substr($patient->user->nom, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-heading font-bold text-slate-900">{{ $patient->user->full_name }}</h2>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="font-mono text-xs font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-md border border-brand-200">
                                {{ $patient->numero_patient }}
                            </span>
                            <span class="text-xs text-slate-400">Inscrit le {{ $patient->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Compte Actif
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 space-y-1">
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Date de Naissance</div>
                    <div class="font-bold text-slate-800">
                        {{ $patient->date_naissance ? $patient->date_naissance->format('d/m/Y') : 'Non renseignée' }}
                        @if($patient->age)
                            <span class="text-slate-500 font-normal">({{ $patient->age }} ans)</span>
                        @endif
                    </div>
                </div>

                <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 space-y-1">
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Genre / Sexe</div>
                    <div class="font-bold text-slate-800">{{ $patient->sexe ?? 'Non spécifié' }}</div>
                </div>

                <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 space-y-1">
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Téléphone Principal</div>
                    <div class="font-mono font-bold text-brand-700">{{ $patient->user->telephone }}</div>
                </div>

                <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 space-y-1">
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Adresse E-mail</div>
                    <div class="font-medium text-slate-700 truncate">{{ $patient->user->email }}</div>
                </div>

                <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 space-y-1">
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Contact d'Urgence</div>
                    <div class="font-bold text-slate-800">{{ $patient->contact_urgence ?? 'Aucun contact déclaré' }}</div>
                </div>

                <div class="p-3.5 bg-slate-50/80 rounded-xl border border-slate-100 space-y-1">
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Adresse Domicile</div>
                    <div class="font-medium text-slate-700 truncate">{{ $patient->adresse ?? 'Non renseignée' }}</div>
                </div>
            </div>
        </div>

        <!-- Synthèse de l'Assiduité -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-card space-y-4 flex flex-col justify-between">
            <div class="space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-brand-600"></i>
                    Indice d'Assiduité Médicale
                </h3>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-heading font-extrabold text-slate-900">{{ $tauxAssiduite }}%</span>
                    <span class="text-xs text-slate-500 font-medium">de ponctualité</span>
                </div>
                <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                    <div class="bg-brand-500 h-full rounded-full transition-all duration-500" style="width: {{ $tauxAssiduite }}%"></div>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Calculé sur la base des consultations effectuées face aux rendez-vous non honorés.
                </p>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Médecins Consultés</div>
                <div class="flex flex-wrap gap-1.5">
                    @forelse($medecinsConsultes as $medecin)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold">
                            <i data-lucide="user-round" class="w-3 h-3 text-slate-400"></i>
                            Dr. {{ $medecin->nom_complet }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400 italic">Aucune consultation enregistrée</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Indicateurs Chiffrés -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-slate-400 text-xs font-medium flex items-center gap-1">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-brand-600"></i>
                Total RDV
            </div>
            <div class="text-2xl font-heading font-extrabold text-slate-900">{{ $totalRdv }}</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-slate-400 text-xs font-medium flex items-center gap-1">
                <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                Effectués
            </div>
            <div class="text-2xl font-heading font-extrabold text-emerald-600">{{ $effectues }}</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-slate-400 text-xs font-medium flex items-center gap-1">
                <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i>
                À venir
            </div>
            <div class="text-2xl font-heading font-extrabold text-amber-600">{{ $aVenir }}</div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card space-y-1">
            <div class="text-slate-400 text-xs font-medium flex items-center gap-1">
                <i data-lucide="user-x" class="w-3.5 h-3.5 text-rose-600"></i>
                Absences / Annulés
            </div>
            <div class="text-2xl font-heading font-extrabold text-rose-600">{{ $absents + $annules }}</div>
        </div>
    </div>

    <!-- Parcours de Soins : Historique Chronologique & Attestations -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card p-6 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-heading font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-brand-600"></i>
                    Parcours de Soins & Historique Médical
                </h3>
                <p class="text-xs text-slate-500">Traçabilité complète des rendez-vous et accès direct aux attestations officielles</p>
            </div>
        </div>

        @if($patient->rendezVous->isEmpty())
            <div class="text-center py-12 space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                    <i data-lucide="calendar-x" class="w-6 h-6"></i>
                </div>
                <div class="text-sm font-bold text-slate-700">Aucune consultation enregistrée</div>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">Ce patient n'a pas encore de rendez-vous dans le système hospitalier.</p>
            </div>
        @else
            <!-- Version Mobile (< 640px) -->
            <div class="block sm:hidden space-y-3">
                @foreach($patient->rendezVous as $rdv)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-slate-900">{{ $rdv->reference_rdv }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $rdv->statut_badge['bg'] }}">
                                {{ $rdv->statut_badge['label'] }}
                            </span>
                        </div>

                        <div>
                            <div class="text-sm font-bold text-slate-900">Dr. {{ $rdv->medecin->nom_complet }}</div>
                            <div class="text-xs text-brand-700 font-medium">{{ $rdv->specialite->nom }}</div>
                            <div class="text-xs text-slate-500 mt-1">
                                {{ $rdv->date_rdv->format('d/m/Y') }} à {{ substr($rdv->heure_rdv, 0, 5) }}
                                @if($rdv->medecin->bureau)
                                    — {{ $rdv->medecin->bureau }}
                                @endif
                            </div>
                        </div>

                        @if($rdv->motif)
                            <div class="text-xs text-slate-600 bg-white p-2.5 rounded-lg border border-slate-200/60">
                                <span class="font-bold text-slate-400 text-[10px] uppercase block">Motif</span>
                                {{ $rdv->motif }}
                            </div>
                        @endif

                        <div class="pt-2 border-t border-slate-200/60">
                            <a href="{{ route('admin.rendez-vous.attestation', $rdv->id) }}" target="_blank"
                               class="w-full py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-800 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs">
                                <i data-lucide="printer" class="w-3.5 h-3.5 text-brand-600"></i>
                                Attestation / Reçu PDF
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Version Desktop (>= 640px) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-3">Référence</th>
                            <th class="py-3 px-3">Date & Heure</th>
                            <th class="py-3 px-3">Praticien</th>
                            <th class="py-3 px-3">Spécialité & Lieu</th>
                            <th class="py-3 px-3">Motif</th>
                            <th class="py-3 px-3">Statut</th>
                            <th class="py-3 px-3 text-right">Attestation</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($patient->rendezVous as $rdv)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-3 font-mono font-bold text-slate-900">
                                    {{ $rdv->reference_rdv }}
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="font-bold text-slate-900">{{ $rdv->date_rdv->format('d/m/Y') }}</div>
                                    <div class="text-slate-400 font-mono text-[11px]">{{ substr($rdv->heure_rdv, 0, 5) }}</div>
                                </td>
                                <td class="py-3.5 px-3 font-bold text-slate-900">
                                    Dr. {{ $rdv->medecin->nom_complet }}
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="text-brand-700 font-bold">{{ $rdv->specialite->nom }}</div>
                                    <div class="text-slate-400 text-[11px]">{{ $rdv->medecin->bureau ?? $rdv->medecin->service }}</div>
                                </td>
                                <td class="py-3.5 px-3 text-slate-600 max-w-xs truncate">
                                    {{ $rdv->motif ?? '—' }}
                                </td>
                                <td class="py-3.5 px-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $rdv->statut_badge['bg'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $rdv->statut_badge['dot'] }}"></span>
                                        {{ $rdv->statut_badge['label'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    <a href="{{ route('admin.rendez-vous.attestation', $rdv->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-brand-50 text-slate-700 hover:text-brand-700 text-xs font-bold transition">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        Attestation
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
