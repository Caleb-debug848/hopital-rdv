@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- En-tête -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight">Registre des Patients</h1>
            <p class="text-xs text-slate-500">Liste des patients enregistrés — Données protégées conformément à la Loi n°2013-450</p>
        </div>
    </div>

    <!-- Recherche -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-card">
        <form action="{{ route('admin.patients.index') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher par nom, prénom, téléphone, numéro de dossier..."
                   class="w-full px-4 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none">
            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                Rechercher
            </button>
        </form>
    </div>

    <!-- Tableau des Patients -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-card overflow-hidden p-6 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-3">N° Dossier</th>
                        <th class="py-3 px-3">Nom & Prénom</th>
                        <th class="py-3 px-3">Téléphone</th>
                        <th class="py-3 px-3">Email</th>
                        <th class="py-3 px-3">Sexe</th>
                        <th class="py-3 px-3">Date d'inscription</th>
                        <th class="py-3 px-3 text-right">Consultations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($patients as $patient)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-3 font-mono font-bold text-slate-900">
                                {{ $patient->numero_patient }}
                            </td>
                            <td class="py-3.5 px-3 font-bold text-slate-900">
                                {{ $patient->user->full_name }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600 font-mono">
                                {{ $patient->user->telephone }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-500">
                                {{ $patient->user->email }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-600">
                                {{ $patient->sexe ?? '—' }}
                            </td>
                            <td class="py-3.5 px-3 text-slate-400">
                                {{ $patient->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-bold text-slate-800">
                                <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 text-[11px]">
                                    {{ $patient->rendez_vous_count }} RDV
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pt-4">
            {{ $patients->links() }}
        </div>
    </div>

</div>
@endsection
