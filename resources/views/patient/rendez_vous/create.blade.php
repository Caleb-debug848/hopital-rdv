@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 py-2" x-data="appointmentBooking()">

    <!-- Titre et fil d'Ariane -->
    <div class="space-y-1">
        <a href="{{ route('patient.rendez-vous.index') }}" class="text-xs font-semibold text-brand-600 hover:underline inline-flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Retour à mes rendez-vous</span>
        </a>
        <h1 class="text-2xl font-heading font-extrabold text-slate-900 tracking-tight">Prendre un Rendez-vous</h1>
        <p class="text-xs text-slate-500">Sélectionnez la spécialité, le praticien et le créneau horaire souhaité en quelques clics.</p>
    </div>

    <!-- Stepper Visuel 4 Étapes -->
    <div class="grid grid-cols-4 gap-1.5 sm:gap-4 text-center">
        <div class="p-2 sm:p-3 rounded-xl border transition-all" :class="step >= 1 ? 'bg-brand-50 border-brand-300 text-brand-900 font-bold' : 'bg-white border-slate-200/80 text-slate-400'">
            <div class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-400">Étape 1</div>
            <div class="text-[11px] sm:text-sm mt-0.5 font-bold truncate">Spécialité</div>
        </div>
        <div class="p-2 sm:p-3 rounded-xl border transition-all" :class="step >= 2 ? 'bg-brand-50 border-brand-300 text-brand-900 font-bold' : 'bg-white border-slate-200/80 text-slate-400'">
            <div class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-400">Étape 2</div>
            <div class="text-[11px] sm:text-sm mt-0.5 font-bold truncate">Médecin</div>
        </div>
        <div class="p-2 sm:p-3 rounded-xl border transition-all" :class="step >= 3 ? 'bg-brand-50 border-brand-300 text-brand-900 font-bold' : 'bg-white border-slate-200/80 text-slate-400'">
            <div class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-400">Étape 3</div>
            <div class="text-[11px] sm:text-sm mt-0.5 font-bold truncate">Créneau</div>
        </div>
        <div class="p-2 sm:p-3 rounded-xl border transition-all" :class="step >= 4 ? 'bg-brand-50 border-brand-300 text-brand-900 font-bold' : 'bg-white border-slate-200/80 text-slate-400'">
            <div class="text-[9px] sm:text-[10px] uppercase font-bold text-slate-400">Étape 4</div>
            <div class="text-[11px] sm:text-sm mt-0.5 font-bold truncate">Valider</div>
        </div>
    </div>

    <!-- Formulaire Global -->
    <form action="{{ route('patient.rendez-vous.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Champs masqués pour soumission -->
        <input type="hidden" name="specialite_id" :value="selectedSpecialite">
        <input type="hidden" name="medecin_id" :value="selectedMedecin">
        <input type="hidden" name="date_rdv" :value="selectedDate">
        <input type="hidden" name="heure_rdv" :value="selectedSlot">

        <!-- ÉTAPE 1 : Choix de la Spécialité -->
        <div class="bg-white p-4 sm:p-8 rounded-2xl border border-slate-200/80 shadow-card space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">1</span>
                <div>
                    <h2 class="text-base font-heading font-extrabold text-slate-900">Choisissez la Spécialité</h2>
                    <p class="text-xs text-slate-500">Sélectionnez le département médical correspondant à votre motif</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3 pt-2">
                @foreach($specialites as $spe)
                    <button type="button" 
                            @click="selectSpecialite({{ $spe->id }}, '{{ addslashes($spe->nom) }}')"
                            class="p-3.5 sm:p-4 rounded-xl border text-left transition-all flex flex-col justify-between gap-3 group min-h-[90px]"
                            :class="selectedSpecialite == {{ $spe->id }} ? 'bg-slate-900 text-white border-slate-900 shadow-md' : 'bg-white text-slate-700 border-slate-200 hover:border-brand-500 hover:bg-slate-50'">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-lg flex items-center justify-center text-sm shrink-0" 
                             :class="selectedSpecialite == {{ $spe->id }} ? 'bg-white/10 text-white' : 'bg-slate-100 text-slate-700 group-hover:bg-brand-50 group-hover:text-brand-600'">
                            <i data-lucide="activity" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="font-bold text-xs sm:text-sm leading-snug">{{ $spe->nom }}</div>
                            <div class="text-[11px] opacity-70 mt-0.5">{{ count($spe->medecins) }} médecin(s)</div>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- ÉTAPE 2 : Choix du Médecin -->
        <div class="bg-white p-4 sm:p-8 rounded-2xl border border-slate-200/80 shadow-card space-y-4" x-show="selectedSpecialite" x-cloak>
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">2</span>
                <div>
                    <h2 class="text-base font-heading font-extrabold text-slate-900">Choisissez le Praticien</h2>
                    <p class="text-xs text-slate-500">Médecins disponibles pour la spécialité <strong class="text-slate-800" x-text="selectedSpecialiteName"></strong></p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 pt-2">
                @foreach($specialites as $spe)
                    @foreach($spe->medecins as $med)
                        <div x-show="selectedSpecialite == {{ $spe->id }}" class="contents">
                            <button type="button"
                                    @click="selectMedecin({{ $med->id }}, '{{ addslashes($med->nom_complet) }}', '{{ addslashes($med->bureau ?? '') }}')"
                                    class="p-4 sm:p-5 rounded-xl border text-left transition-all flex items-start gap-3 sm:gap-4"
                                    :class="selectedMedecin == {{ $med->id }} ? 'bg-brand-50/70 border-brand-500 ring-2 ring-brand-500/20' : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50'">
                                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center text-sm font-bold shrink-0">
                                    <i data-lucide="stethoscope" class="w-5 h-5 text-slate-600"></i>
                                </div>
                                <div class="space-y-1 min-w-0">
                                    <div class="font-heading font-bold text-sm text-slate-900 truncate">{{ $med->nom_complet }}</div>
                                    <div class="text-xs text-slate-500 truncate">{{ $med->service ?? 'Service Hospitalier' }}</div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-1">
                                        <i data-lucide="calendar" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                        <span class="truncate">Jours : <strong class="text-slate-700">{{ is_array($med->jours_consultation) ? implode(', ', $med->jours_consultation) : 'Sur RDV' }}</strong></span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                        <span>Horaires : {{ substr($med->heure_debut_defaut, 0, 5) }} - {{ substr($med->heure_fin_defaut, 0, 5) }}</span>
                                    </div>
                                </div>
                            </button>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        <!-- ÉTAPE 3 : Choix de la Date & Créneaux Horaires (CALENDRIER INTERACTIF MÉDICAL) -->
        <div class="bg-white p-4 sm:p-8 rounded-2xl border border-slate-200/80 shadow-card space-y-6" x-show="selectedMedecin" x-cloak>
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-xs">3</span>
                <div>
                    <h2 class="text-base font-heading font-extrabold text-slate-900">Date & Créneaux Disponibles</h2>
                    <p class="text-xs text-slate-500">
                        Calendrier des consultations pour <strong class="text-brand-600" x-text="selectedMedecinName"></strong>
                    </p>
                </div>
            </div>

            <!-- NOUVEAU CALENDRIER INTERACTIF AVEC JOURS GRISÉS -->
            <div class="space-y-3 bg-slate-50/70 p-4 sm:p-6 rounded-2xl border border-slate-200/80">
                
                <!-- En-tête de navigation mois & année -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar" class="w-5 h-5 text-brand-600"></i>
                        <span class="font-heading font-extrabold text-base text-slate-900" x-text="monthNames[currentMonthIndex] + ' ' + currentYear"></span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button type="button" 
                                @click="prevMonth()" 
                                :disabled="isPrevMonthDisabled()"
                                class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed transition"
                                title="Mois précédent">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </button>
                        <button type="button" 
                                @click="nextMonth()" 
                                class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 transition"
                                title="Mois suivant">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Jours de la semaine -->
                <div class="grid grid-cols-7 gap-1.5 sm:gap-2 text-center text-[11px] font-bold uppercase tracking-wider text-slate-400 py-1">
                    <div>Lun</div>
                    <div>Mar</div>
                    <div>Mer</div>
                    <div>Jeu</div>
                    <div>Ven</div>
                    <div class="text-rose-400">Sam</div>
                    <div class="text-rose-400">Dim</div>
                </div>

                <!-- Grille interactive des jours -->
                <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                    <!-- Jours vides avant le premier jour du mois -->
                    <template x-for="blank in getLeadingBlanks()" :key="'blank-' + blank">
                        <div class="h-10 sm:h-12 rounded-xl bg-transparent"></div>
                    </template>

                    <!-- Jours réels du mois -->
                    <template x-for="day in getMonthDays()" :key="day.dateString">
                        <div>
                            <!-- 1. Jour où le médecin CONSULTE et disponible (Actif) -->
                            <button type="button"
                                    x-show="day.canSelect"
                                    @click="selectDate(day.dateString)"
                                    :title="'Consultation disponible avec ' + selectedMedecinName"
                                    class="w-full h-10 sm:h-12 rounded-xl text-xs font-bold transition-all relative flex flex-col items-center justify-center"
                                    :class="selectedDate === day.dateString 
                                            ? 'bg-brand-600 text-white shadow-md ring-2 ring-brand-600/30 scale-105 border border-brand-600 font-extrabold' 
                                            : 'bg-white text-slate-800 border border-slate-200 hover:border-brand-500 hover:bg-brand-50/80 hover:text-brand-700 shadow-2xs cursor-pointer'">
                                <span x-text="day.dayNumber"></span>
                                <span class="w-1.5 h-1.5 rounded-full absolute bottom-1.5"
                                      :class="selectedDate === day.dateString ? 'bg-white' : 'bg-emerald-500'"></span>
                            </button>

                            <!-- 2. Jour où le médecin NE consulte PAS (Grisé & Bloqué) -->
                            <div x-show="!day.isPast && !day.isDoctorWorking"
                                 title="Le médecin ne consulte pas ce jour"
                                 class="w-full h-10 sm:h-12 rounded-xl bg-slate-200/50 text-slate-400 border border-slate-200/40 text-xs font-medium flex flex-col items-center justify-center cursor-not-allowed select-none opacity-40">
                                <span class="line-through" x-text="day.dayNumber"></span>
                                <span class="text-[8px] uppercase tracking-tighter text-slate-400">Fermé</span>
                            </div>

                            <!-- 3. Jour Passé (Grisé) -->
                            <div x-show="day.isPast"
                                 class="w-full h-10 sm:h-12 rounded-xl bg-slate-100/40 text-slate-300 text-xs flex items-center justify-center cursor-not-allowed select-none opacity-30">
                                <span x-text="day.dayNumber"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Légende explicative sous le calendrier -->
                <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-slate-200 text-[11px] text-slate-500">
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="font-semibold text-slate-700">Jour de consultation</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                            <span class="text-slate-400">Non disponible (Grisé)</span>
                        </span>
                    </div>

                    <div x-show="selectedDate" class="text-xs font-bold text-brand-700 bg-brand-50 px-3 py-1 rounded-lg border border-brand-200">
                        <span>Date choisie : </span>
                        <span x-text="formatReadableDate(selectedDate)"></span>
                    </div>
                </div>

            </div>

            <!-- Zone d'affichage des créneaux horaires -->
            <div class="space-y-4 pt-2">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-brand-600"></i>
                        <span>Créneaux horaires disponibles</span>
                    </div>
                    <!-- Légende créneaux -->
                    <div class="flex items-center gap-3 text-[11px]">
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Libre</span>
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-rose-400"></span> Réservé</span>
                    </div>
                </div>

                <!-- Loader de chargement -->
                <div x-show="loadingSlots" class="py-8 text-center text-xs text-slate-400 flex items-center justify-center gap-2">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-brand-600"></i>
                    <span>Calcul des créneaux libres en temps réel...</span>
                </div>

                <!-- Erreur ou Pas de consultation ce jour -->
                <div x-show="!loadingSlots && slotsData && slotsData.status !== 'success'" class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center space-y-1">
                    <div class="text-xs font-bold text-slate-700" x-text="slotsData ? slotsData.message : ''"></div>
                    <p class="text-xs text-slate-400">Veuillez sélectionner un jour de consultation valide dans le calendrier.</p>
                </div>

                <!-- Grille des créneaux -->
                <div x-show="!loadingSlots && slotsData && slotsData.status === 'success' && slotsData.slots.length > 0" class="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
                    <template x-for="slot in (slotsData ? slotsData.slots : [])" :key="slot.heure">
                        <div>
                            <!-- Créneau Libre -->
                            <button type="button" 
                                    x-show="slot.statut === 'disponible'"
                                    @click="selectSlot(slot.heure)"
                                    class="w-full py-2.5 px-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 min-h-[44px]"
                                    :class="selectedSlot === slot.heure ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-600/30' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="selectedSlot === slot.heure ? 'bg-white' : 'bg-emerald-500'"></span>
                                <span x-text="slot.heure"></span>
                            </button>

                            <!-- Créneau Réservé -->
                            <button type="button" 
                                    x-show="slot.statut === 'reserve'"
                                    disabled
                                    class="w-full py-2.5 px-2 rounded-xl text-xs font-medium bg-rose-50 text-rose-400 border border-rose-100 cursor-not-allowed flex items-center justify-center gap-1.5 min-h-[44px]">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                <span x-text="slot.heure"></span>
                            </button>

                            <!-- Créneau Passé -->
                            <button type="button" 
                                    x-show="slot.statut === 'passe'"
                                    disabled
                                    class="w-full py-2.5 px-2 rounded-xl text-xs font-medium bg-slate-100 text-slate-400 cursor-not-allowed flex items-center justify-center gap-1 min-h-[44px]">
                                <span x-text="slot.heure"></span>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- FONCTIONNALITÉ LISTE D'ATTENTE SANS EMOJIS -->
                <div x-show="!loadingSlots && slotsData && (slotsData.is_fully_booked || (slotsData.status === 'success' && slotsData.available_count === 0))" 
                     class="p-4 sm:p-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 space-y-3">
                    <div class="flex items-center gap-2 font-heading font-bold text-xs sm:text-sm text-amber-900">
                        <i data-lucide="bell-ring" class="w-4 h-4 text-amber-700"></i>
                        <span>Tous les créneaux sont complets — Rejoindre la Liste d'Attente</span>
                    </div>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        Tous les créneaux pour le <strong x-text="selectedMedecinName"></strong> à cette date sont réservés. Vous pouvez vous inscrire sur la liste d'attente pour être notifié immédiatement si un désistement a lieu.
                    </p>
                    <button type="button" @click="submitWaitingList()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition min-h-[44px]">
                        <i data-lucide="bell" class="w-3.5 h-3.5"></i>
                        Me prévenir dès qu'un créneau se libère
                    </button>
                </div>
            </div>
        </div>

        <!-- ÉTAPE 4 : Motif & Confirmation -->
        <div class="bg-white p-4 sm:p-8 rounded-2xl border border-slate-200/80 shadow-card space-y-6" x-show="selectedSlot" x-cloak>
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0">4</span>
                <div>
                    <h2 class="text-base font-heading font-extrabold text-slate-900">Motif & Validation</h2>
                    <p class="text-xs text-slate-500">Vérifiez les détails et validez votre réservation</p>
                </div>
            </div>

            <!-- Récapitulatif -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2 text-xs text-slate-700">
                <div class="font-bold text-xs text-slate-900 mb-2 uppercase tracking-wider">Récapitulatif de la réservation :</div>
                <div class="flex justify-between py-1.5 border-b border-slate-200/60">
                    <span class="text-slate-500">Spécialité :</span>
                    <strong class="text-slate-900" x-text="selectedSpecialiteName"></strong>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-200/60">
                    <span class="text-slate-500">Médecin Praticien :</span>
                    <strong class="text-slate-900" x-text="selectedMedecinName"></strong>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-500">Date & Créneau :</span>
                    <strong class="text-brand-700"><span x-text="formatReadableDate(selectedDate)"></span> à <span x-text="selectedSlot"></span></strong>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Motif de consultation (facultatif)</label>
                <input type="text" name="motif" placeholder="Ex: Consultation de contrôle, avis spécialisé, renouvellement..."
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:outline-none min-h-[44px]">
            </div>

            <button type="submit" class="w-full py-3.5 px-6 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-heading font-bold text-sm shadow-xs transition flex items-center justify-center gap-2 min-h-[48px]">
                <i data-lucide="check" class="w-4 h-4"></i>
                Confirmer la réservation du rendez-vous
            </button>
        </div>

    </form>

    <!-- Formulaire caché pour soumission Liste d'attente -->
    <form id="waitingListForm" action="{{ route('patient.rendez-vous.waiting-list') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="specialite_id" :value="selectedSpecialite">
        <input type="hidden" name="medecin_id" :value="selectedMedecin">
        <input type="hidden" name="date_souhaitee" :value="selectedDate">
    </form>

</div>

@push('scripts')
<script>
function appointmentBooking() {
    return {
        step: 1,
        selectedSpecialite: '{{ $selectedSpecialiteId ?? '' }}',
        selectedSpecialiteName: '',
        selectedMedecin: '{{ $selectedMedecinId ?? '' }}',
        selectedMedecinName: '{{ $selectedMedecin ? addslashes($selectedMedecin->nom_complet) : '' }}',
        selectedMedecinOffice: '',
        selectedDate: '{{ $selectedDate ?? date('Y-m-d', strtotime('+1 day')) }}',
        selectedSlot: '',
        slotsData: @json($slotsData ?? null),
        loadingSlots: false,

        // Calendrier interactif
        currentYear: new Date().getFullYear(),
        currentMonthIndex: new Date().getMonth(),
        todayDateString: '{{ date('Y-m-d') }}',
        monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        daysOfWeekFr: ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'],

        // Dictionnaire des médecins avec leurs jours de consultation
        medecinsMap: {
            @foreach($specialites as $spe)
                @foreach($spe->medecins as $med)
                    {{ $med->id }}: {
                        id: {{ $med->id }},
                        nom: '{{ addslashes($med->nom_complet) }}',
                        bureau: '{{ addslashes($med->bureau ?? '') }}',
                        jours: @json($med->jours_consultation ?? []),
                    },
                @endforeach
            @endforeach
        },

        init() {
            if (this.selectedDate) {
                const parts = this.selectedDate.split('-');
                if (parts.length === 3) {
                    this.currentYear = parseInt(parts[0], 10);
                    this.currentMonthIndex = parseInt(parts[1], 10) - 1;
                }
            }

            if (this.selectedMedecin) {
                this.step = 3;
                this.fetchSlots();
            }
        },

        selectSpecialite(id, name) {
            this.selectedSpecialite = id;
            this.selectedSpecialiteName = name;
            this.selectedMedecin = '';
            this.selectedMedecinName = '';
            this.selectedSlot = '';
            this.slotsData = null;
            this.step = 2;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        selectMedecin(id, name, office) {
            this.selectedMedecin = id;
            this.selectedMedecinName = name;
            this.selectedMedecinOffice = office;
            this.selectedSlot = '';
            this.step = 3;
            
            // Si la date actuellement sélectionnée n'est pas un jour de consultation, trouver le premier jour disponible
            this.ensureValidDateForDoctor();
            this.fetchSlots();
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        getDoctorConsultationDays() {
            if (!this.selectedMedecin || !this.medecinsMap[this.selectedMedecin]) return [];
            return (this.medecinsMap[this.selectedMedecin].jours || []).map(j => j.toLowerCase());
        },

        getLeadingBlanks() {
            const firstDay = new Date(this.currentYear, this.currentMonthIndex, 1).getDay();
            // Lundi = 0, Mardi = 1, ... Dimanche = 6
            const blankCount = (firstDay + 6) % 7;
            return Array.from({ length: blankCount }, (_, i) => i);
        },

        getMonthDays() {
            const daysInMonth = new Date(this.currentYear, this.currentMonthIndex + 1, 0).getDate();
            const doctorDays = this.getDoctorConsultationDays();
            const days = [];

            for (let d = 1; d <= daysInMonth; d++) {
                const dateObj = new Date(this.currentYear, this.currentMonthIndex, d);
                const monthPadded = String(this.currentMonthIndex + 1).padStart(2, '0');
                const dayPadded = String(d).padStart(2, '0');
                const dateString = `${this.currentYear}-${monthPadded}-${dayPadded}`;
                
                const dayOfWeekIndex = dateObj.getDay();
                const dayNameFr = this.daysOfWeekFr[dayOfWeekIndex];
                
                const isPast = dateString < this.todayDateString;
                const isDoctorWorking = doctorDays.length === 0 || doctorDays.includes(dayNameFr);
                const canSelect = !isPast && isDoctorWorking;

                days.push({
                    dayNumber: d,
                    dateString: dateString,
                    dayOfWeek: dayNameFr,
                    isPast: isPast,
                    isDoctorWorking: isDoctorWorking,
                    canSelect: canSelect
                });
            }

            return days;
        },

        ensureValidDateForDoctor() {
            const days = this.getMonthDays();
            const currentDayObj = days.find(d => d.dateString === this.selectedDate);
            if (!currentDayObj || !currentDayObj.canSelect) {
                const firstAvailable = days.find(d => d.canSelect);
                if (firstAvailable) {
                    this.selectedDate = firstAvailable.dateString;
                }
            }
        },

        isPrevMonthDisabled() {
            const now = new Date();
            return this.currentYear === now.getFullYear() && this.currentMonthIndex <= now.getMonth();
        },

        prevMonth() {
            if (this.isPrevMonthDisabled()) return;
            if (this.currentMonthIndex === 0) {
                this.currentMonthIndex = 11;
                this.currentYear--;
            } else {
                this.currentMonthIndex--;
            }
        },

        nextMonth() {
            if (this.currentMonthIndex === 11) {
                this.currentMonthIndex = 0;
                this.currentYear++;
            } else {
                this.currentMonthIndex++;
            }
        },

        selectDate(dateStr) {
            this.selectedDate = dateStr;
            this.selectedSlot = '';
            this.fetchSlots();
        },

        formatReadableDate(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            const d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            const dayName = this.daysOfWeekFr[d.getDay()];
            const capitalizedDay = dayName.charAt(0).toUpperCase() + dayName.slice(1);
            const monthName = this.monthNames[d.getMonth()];
            return `${capitalizedDay} ${d.getDate()} ${monthName} ${d.getFullYear()}`;
        },

        selectSlot(heure) {
            this.selectedSlot = heure;
            this.step = 4;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        async fetchSlots() {
            if (!this.selectedMedecin || !this.selectedDate) return;
            this.loadingSlots = true;
            this.selectedSlot = '';

            try {
                const response = await fetch(`{{ route('patient.rendez-vous.slots') }}?medecin_id=${this.selectedMedecin}&date=${this.selectedDate}`);
                const data = await response.json();
                this.slotsData = data;
            } catch (err) {
                console.error("Erreur chargement créneaux", err);
            } finally {
                this.loadingSlots = false;
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            }
        },

        submitWaitingList() {
            document.getElementById('waitingListForm').submit();
        }
    }
}
</script>
@endpush
@endsection
