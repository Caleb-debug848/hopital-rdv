@props([
    'variant' => 'default', // 'default', 'white', 'icon'
    'class' => 'h-10 w-auto',
    'showText' => true
])

@if($variant === 'icon')
    <img src="{{ asset('images/logo-icon.svg') }}" alt="Hôpital RDV Logo" {{ $attributes->merge(['class' => $class]) }}>
@elseif($variant === 'white')
    <div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
        <img src="{{ asset('images/logo-icon.svg') }}" alt="Hôpital RDV" class="{{ $class }}">
        @if($showText)
            <div class="flex flex-col whitespace-nowrap overflow-hidden">
                <span class="font-heading font-extrabold text-base tracking-tight text-white leading-tight">
                    Hôpital <span class="text-blue-400">RDV</span>
                </span>
                <span class="text-[9px] uppercase font-bold tracking-widest text-slate-400">Santé Connectée</span>
            </div>
        @endif
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
        <img src="{{ asset('images/logo-icon.svg') }}" alt="Hôpital RDV" class="{{ $class }}">
        @if($showText)
            <div class="flex flex-col whitespace-nowrap overflow-hidden">
                <span class="font-heading font-extrabold text-base tracking-tight text-slate-900 leading-tight">
                    Hôpital <span class="text-brand-600">RDV</span>
                </span>
                <span class="text-[9px] uppercase font-bold tracking-widest text-slate-400">Santé Connectée</span>
            </div>
        @endif
    </div>
@endif
