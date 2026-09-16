<?php

namespace Tests\Feature;

use App\Models\Indisponibilite;
use App\Models\ListeAttente;
use App\Models\Medecin;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Specialite;
use App\Models\User;
use App\Services\AppointmentSlotService;
use App\Services\ReminderService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Scénario 1 : Prise de rendez-vous complète par un patient avec notifications.
     */
    public function test_patient_can_book_appointment_and_receive_notification(): void
    {
        $patientUser = User::where('role', 'patient')->first();
        // Sélectionner Dr Bamba (Médecine générale qui consulte du lundi au vendredi)
        $medecin = Medecin::whereHas('specialite', function($q) {
            $q->where('slug', 'medecine-generale');
        })->first();
        $targetDate = Carbon::now()->next(Carbon::TUESDAY)->toDateString();

        $response = $this->actingAs($patientUser)->post(route('patient.rendez-vous.store'), [
            'medecin_id' => $medecin->id,
            'specialite_id' => $medecin->specialite_id,
            'date_rdv' => $targetDate,
            'heure_rdv' => '08:30',
            'motif' => 'Consultation de routine et bilan de santé',
        ]);

        $response->assertRedirect();
        
        // Vérification de la création en base
        $this->assertDatabaseHas('rendez_vous', [
            'medecin_id' => $medecin->id,
            'heure_rdv' => '08:30',
            'statut' => 'confirme',
        ]);

        // Vérification des notifications pour le patient et le médecin
        $this->assertDatabaseHas('notifications', [
            'user_id' => $patientUser->id,
            'type' => 'confirmation',
        ]);

        if ($medecin->user_id) {
            $this->assertDatabaseHas('notifications', [
                'user_id' => $medecin->user_id,
                'type' => 'confirmation',
            ]);
        }
    }

    /**
     * Scénario 2 : Pointage au guichet par le secrétariat et consultation par le médecin.
     */
    public function test_secretary_check_in_and_doctor_completion(): void
    {
        $secretaireUser = User::where('role', 'secretaire')->first();
        $medecin = Medecin::with('user')->where('statut', 'actif')->first();
        $patient = Patient::first();

        $rdv = RendezVous::create([
            'patient_id' => $patient->id,
            'medecin_id' => $medecin->id,
            'specialite_id' => $medecin->specialite_id,
            'date_rdv' => Carbon::today()->toDateString(),
            'heure_rdv' => '09:00:00',
            'statut' => 'confirme',
            'motif' => 'Suivi cardiologique',
        ]);

        // 1. Secrétaire marque le patient comme arrivé au guichet
        $response1 = $this->actingAs($secretaireUser)->post(route('secretaire.rdv.status', $rdv->id), [
            'statut' => 'arrive',
        ]);
        $response1->assertRedirect();
        $this->assertDatabaseHas('rendez_vous', [
            'id' => $rdv->id,
            'statut' => 'arrive',
        ]);

        // 2. Médecin consulte et termine le rendez-vous
        $response2 = $this->actingAs($medecin->user)->post(route('medecin.rdv.status', $rdv->id), [
            'statut' => 'termine',
        ]);
        $response2->assertRedirect();
        $this->assertDatabaseHas('rendez_vous', [
            'id' => $rdv->id,
            'statut' => 'termine',
        ]);
    }

    /**
     * Scénario 3 : Liste d'attente automatisée et libération de créneau après annulation.
     */
    public function test_waiting_list_receives_notification_on_appointment_cancellation(): void
    {
        $patient1User = User::where('role', 'patient')->first();
        $patient2User = User::create([
            'nom' => 'Kouassi',
            'prenom' => 'Michel',
            'email' => 'michel.kouassi@test.com',
            'password' => bcrypt('password123'),
            'role' => 'patient',
            'telephone' => '+225 0102030405',
        ]);
        $patient2 = Patient::create(['user_id' => $patient2User->id]);

        $medecin = Medecin::first();
        $targetDate = Carbon::tomorrow()->isSunday() ? Carbon::tomorrow()->addDay()->toDateString() : Carbon::tomorrow()->toDateString();

        // 1. Patient 1 réserve
        $rdv = RendezVous::create([
            'patient_id' => $patient1User->patient->id,
            'medecin_id' => $medecin->id,
            'specialite_id' => $medecin->specialite_id,
            'date_rdv' => $targetDate,
            'heure_rdv' => '10:00:00',
            'statut' => 'confirme',
        ]);

        // 2. Patient 2 s'inscrit sur la liste d'attente pour cette même date et médecin
        $this->actingAs($patient2User)->post(route('patient.rendez-vous.waiting-list'), [
            'medecin_id' => $medecin->id,
            'specialite_id' => $medecin->specialite_id,
            'date_souhaitee' => $targetDate,
        ]);

        $this->assertDatabaseHas('listes_attente', [
            'patient_id' => $patient2->id,
            'medecin_id' => $medecin->id,
            'statut' => 'en_attente',
        ]);

        // 3. Patient 1 annule son créneau
        $this->actingAs($patient1User)->post(route('patient.rendez-vous.cancel', $rdv->id), [
            'notes_annulation' => 'Empêchement imprévu',
        ]);

        // 4. Vérifier que Patient 2 a reçu l'alerte de libération de créneau
        $this->assertDatabaseHas('notifications', [
            'user_id' => $patient2User->id,
            'type' => 'liste_attente',
        ]);

        $this->assertDatabaseHas('listes_attente', [
            'patient_id' => $patient2->id,
            'statut' => 'notifie',
        ]);
    }

    /**
     * Scénario 4 : Génération d'attestation PDF et Export du dossier médical.
     */
    public function test_attestation_and_medical_history_export(): void
    {
        $patientUser = User::where('role', 'patient')->first();
        $medecin = Medecin::first();

        $rdv = RendezVous::create([
            'patient_id' => $patientUser->patient->id,
            'medecin_id' => $medecin->id,
            'specialite_id' => $medecin->specialite_id,
            'date_rdv' => Carbon::today()->toDateString(),
            'heure_rdv' => '11:00:00',
            'statut' => 'confirme',
            'motif' => 'Contrôle médical trimestriel',
        ]);

        // 1. Accès à l'attestation imprimable / PDF
        $attestationResponse = $this->actingAs($patientUser)->get(route('patient.rendez-vous.attestation', $rdv->id));
        $attestationResponse->assertStatus(200);
        $attestationResponse->assertSee($rdv->reference_rdv);
        $attestationResponse->assertSee('Attestation de Prise de Rendez-vous');

        // 2. Accès à l'export de l'historique complet
        $exportResponse = $this->actingAs($patientUser)->get(route('patient.historique.export'));
        $exportResponse->assertStatus(200);
        $exportResponse->assertSee('Dossier de Suivi', false);
        $exportResponse->assertSee($rdv->reference_rdv);
    }

    /**
     * Scénario 5 : Simulateur et envoi de rappels SMS / Email.
     */
    public function test_reminder_service_simulation(): void
    {
        $patientUser = User::where('role', 'patient')->first();
        $medecin = Medecin::first();

        $rdv = RendezVous::create([
            'patient_id' => $patientUser->patient->id,
            'medecin_id' => $medecin->id,
            'specialite_id' => $medecin->specialite_id,
            'date_rdv' => Carbon::tomorrow()->toDateString(),
            'heure_rdv' => '14:00:00',
            'statut' => 'confirme',
        ]);

        $response = $this->actingAs($patientUser)->post(route('patient.rendez-vous.rappel', $rdv->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $patientUser->id,
            'type' => 'rappel',
        ]);
    }
}
