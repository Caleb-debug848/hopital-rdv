@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-heading font-extrabold text-slate-900 tracking-tight">Centre de Notifications</h1>
            <p class="text-xs text-slate-500">Rappels de consultations, confirmations et alertes liste d'attente</p>
        </div>
        <form action="{{ route('notifications.read_all') }}" method="POST">
            @csrf
            <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                Tout marquer comme lu
            </button>
        </form>
    </div>

    <!-- Liste des notifications -->
    @if($notifications->isEmpty())
        <div class="bg-white rounded-2xl p-12 border border-slate-200/80 shadow-card text-center space-y-3">
            <div class="w-12 h-12 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto border border-slate-100">
                <i data-lucide="bell-off" class="w-6 h-6"></i>
            </div>
            <div class="text-sm font-bold text-slate-700">Aucune notification</div>
            <p class="text-xs text-slate-400">Vous êtes à jour dans vos messages et convocations.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($notifications as $notif)
                <a href="{{ route('notifications.read', $notif->id) }}" class="block p-4 sm:p-5 rounded-2xl border transition {{ $notif->lu ? 'bg-white border-slate-200/80 hover:border-slate-300' : 'bg-brand-50/70 border-brand-200 hover:bg-brand-50 shadow-xs' }}">
                    <div class="flex items-start gap-4">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 {{ $notif->lu ? 'bg-slate-100 text-slate-500' : 'bg-slate-900 text-brand-400 shadow-xs' }}">
                            @if($notif->type === 'liste_attente')
                                <i data-lucide="bell-ring" class="w-4 h-4 text-amber-400"></i>
                            @elseif($notif->type === 'annulation')
                                <i data-lucide="x-circle" class="w-4 h-4 text-rose-400"></i>
                            @elseif($notif->type === 'confirmation')
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                            @else
                                <i data-lucide="info" class="w-4 h-4 text-brand-400"></i>
                            @endif
                        </div>
                        <div class="space-y-1 flex-grow">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="font-bold text-xs sm:text-sm {{ $notif->lu ? 'text-slate-800' : 'text-brand-950 font-extrabold' }}">{{ $notif->titre }}</h3>
                                <span class="text-[10px] text-slate-400">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $notif->message }}</p>
                        </div>
                    </div>
                </a>
            @endforeach

            <div class="pt-4">
                {{ $notifications->links() }}
            </div>
        </div>
    @endif

</div>
@endsection
