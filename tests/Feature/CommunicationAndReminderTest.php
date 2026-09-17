<?php

namespace Tests\Feature;

use App\Mail\AppointmentConfirmationMail;
use App\Mail\AppointmentReminderMail;
use App\Models\Medecin;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Specialite;
use App\Models\User;
use App\Services\CommunicationHelper;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CommunicationAndReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_communication_helper_salutations(): void
    {
        $morning = Carbon::create(2026, 9, 17, 9, 30, 0, 'Africa/Douala');
        $afternoon = Carbon::create(2026, 9, 17, 14, 0, 0, 'Africa/Douala');
        $evening = Carbon::create(2026, 9, 17, 19, 0, 0, 'Africa/Douala');
        $night = Carbon::create(2026, 9, 17, 23, 45, 0, 'Africa/Douala');

        $this->assertEquals('Bonjour', CommunicationHelper::salutation($morning));
        $this->assertEquals('Bonjour', CommunicationHelper::salutation($afternoon));
        $this->assertEquals('Bonsoir', CommunicationHelper::salutation($evening));
        $this->assertEquals('Bonsoir', CommunicationHelper::salutation($night));
    }

    public function test_whatsapp_message_formatting_has_zero_emojis_and_exact_details(): void
    {
        $specialite = Specialite::create([
            'nom' => 'Cardiologie',
            'description' => 'Maladies cardiovasculaires',
            'icone' => 'heart-pulse',
        ]);

        $doctorUser = User::create([
            'nom' => 'Nzali',
            'prenom' => 'Caleb',
            'email' => 'doctor@test.com',
            'telephone' => '+237695073477',
            'role' => 'medecin',
            'password' => bcrypt('password123'),
        ]);

        $medecin = Medecin::create([
            'user_id' => $doctorUser->id,
            'specialite_id' => $specialite->id,
            'statut' => 'actif',
            'bureau' => 'Cabinet 102',
            'jours_consultation' => ['lundi', 'mardi'],
            'heure_debut_defaut' => '08:00',
            'heure_fin_defaut' => '16:00',
        ]);

        $patientUser = User::create([
            'nom' => 'Kouamé',
            'prenom' => 'Jean',
            'email' => 'patient@test.com',
            'telephone' => '695073477',
            'role' => 'patient',
            'password' => bcrypt('password123'),
        ]);

        $patient = Patient::create([
            'user_id' => $patientUser->id,
            'numero_patient' => 'PAT-12345',
        ]);

        $rdv = RendezVous::create([
            'patient_id' => $patient->id,
            'medecin_id' => $medecin->id,
            'specialite_id' => $specialite->id,
            'date_rdv' => '2026-09-25',
            'heure_rdv' => '14:30:00',
            'statut' => 'confirme',
            'reference_rdv' => 'RDV-TEST01',
        ]);

        $waMessage = CommunicationHelper::formatWhatsAppMessage($rdv);

        // Vérifier l'absence totale d'emojis courants
        $this->assertStringNotContainsString('🏥', $waMessage);
        $this->assertStringNotContainsString('👨‍⚕️', $waMessage);
        $this->assertStringNotContainsString('🩺', $waMessage);
        $this->assertStringNotContainsString('📅', $waMessage);
        $this->assertStringNotContainsString('⏰', $waMessage);
        $this->assertStringNotContainsString('⚠️', $waMessage);

        // Vérifier le contenu
        $this->assertStringContainsString('HOPITAL RDV', $waMessage);
        $this->assertStringContainsString('Jean Kouamé', $waMessage);
        $this->assertStringContainsString('Dr Caleb Nzali', $waMessage);
        $this->assertStringContainsString('Cardiologie', $waMessage);
        $this->assertStringContainsString('14:30', $waMessage);

        // Vérifier la génération de l'URL WhatsApp
        $waUrl = CommunicationHelper::generateWhatsAppUrl($patientUser->telephone, $waMessage);
        $this->assertStringStartsWith('https://wa.me/237695073477', $waUrl);
    }

    public function test_email_templates_render_cleanly_without_emojis(): void
    {
        $specialite = Specialite::create(['nom' => 'Pediatrie', 'icone' => 'baby']);
        $doctorUser = User::create([
            'nom' => 'Kamga',
            'prenom' => 'Paul',
            'email' => 'paul@test.com',
            'telephone' => '677000000',
            'role' => 'medecin',
            'password' => bcrypt('password123'),
        ]);
        $medecin = Medecin::create([
            'user_id' => $doctorUser->id,
            'specialite_id' => $specialite->id,
            'bureau' => 'Pavillon A',
        ]);
        $patientUser = User::create([
            'nom' => 'Eboko',
            'prenom' => 'Marie',
            'email' => 'marie@test.com',
            'telephone' => '699112233',
            'role' => 'patient',
            'password' => bcrypt('password123'),
        ]);
        $patient = Patient::create([
            'user_id' => $patientUser->id,
            'numero_patient' => 'PAT-88888',
        ]);
        $rdv = RendezVous::create([
            'patient_id' => $patient->id,
            'medecin_id' => $medecin->id,
            'specialite_id' => $specialite->id,
            'date_rdv' => '2026-10-01',
            'heure_rdv' => '10:00:00',
            'statut' => 'confirme',
            'reference_rdv' => '#RDV-777777',
        ]);

        // Tester le Mailable de rappel
        $reminderMail = new AppointmentReminderMail($rdv);
        $renderedReminder = $reminderMail->render();
        $this->assertStringContainsString('Marie Eboko', $renderedReminder);
        $this->assertStringContainsString('#RDV-777777', $renderedReminder);
        $this->assertStringNotContainsString('##RDV', $renderedReminder);
        $this->assertStringNotContainsString('🏥', $renderedReminder);

        // Tester le Mailable de confirmation
        $confirmationMail = new AppointmentConfirmationMail($rdv);
        $renderedConfirmation = $confirmationMail->render();
        $this->assertStringContainsString('Marie Eboko', $renderedConfirmation);
        $this->assertStringContainsString('#RDV-777777', $renderedConfirmation);
        $this->assertStringNotContainsString('##RDV', $renderedConfirmation);
        $this->assertStringNotContainsString('🏥', $renderedConfirmation);
    }

    public function test_send_reminders_artisan_command(): void
    {
        Mail::fake();

        $specialite = Specialite::create(['nom' => 'ORL', 'icone' => 'ear']);
        $doctorUser = User::create([
            'nom' => 'Docteur',
            'prenom' => 'Test',
            'email' => 'dr@test.com',
            'telephone' => '677000001',
            'role' => 'medecin',
            'password' => bcrypt('password123'),
        ]);
        $medecin = Medecin::create([
            'user_id' => $doctorUser->id,
            'specialite_id' => $specialite->id,
        ]);
        $patientUser = User::create([
            'nom' => 'Patient',
            'prenom' => 'Demain',
            'email' => 'demain@test.com',
            'telephone' => '699000001',
            'role' => 'patient',
            'password' => bcrypt('password123'),
        ]);
        $patient = Patient::create([
            'user_id' => $patientUser->id,
            'numero_patient' => 'PAT-99999',
        ]);

        $tomorrow = Carbon::tomorrow()->toDateString();
        $rdv = RendezVous::create([
            'patient_id' => $patient->id,
            'medecin_id' => $medecin->id,
            'specialite_id' => $specialite->id,
            'date_rdv' => $tomorrow,
            'heure_rdv' => '09:00:00',
            'statut' => 'confirme',
            'reference_rdv' => '#RDV-DEMAIN1',
        ]);

        $this->artisan('rdv:send-reminders')
            ->assertExitCode(0);

        Mail::assertSent(AppointmentReminderMail::class, function ($mail) use ($rdv) {
            return $mail->hasTo('demain@test.com') && $mail->rdv->id === $rdv->id;
        });
    }
}
