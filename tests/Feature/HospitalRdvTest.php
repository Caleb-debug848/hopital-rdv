<?php

namespace Tests\Feature;

use App\Models\Medecin;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Specialite;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalRdvTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_page_is_accessible(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Hôpital');
    }

    public function test_quick_login_as_patient(): void
    {
        $response = $this->post('/quick-login', ['role' => 'patient']);
        $response->assertRedirect('/patient/dashboard');
        $this->assertAuthenticated();
    }

    public function test_quick_login_as_doctor(): void
    {
        $response = $this->post('/quick-login', ['role' => 'medecin']);
        $response->assertRedirect('/medecin/dashboard');
        $this->assertAuthenticated();
    }

    public function test_quick_login_as_secretary(): void
    {
        $response = $this->post('/quick-login', ['role' => 'secretaire']);
        $response->assertRedirect('/secretaire/guichet');
        $this->assertAuthenticated();
    }

    public function test_quick_login_as_admin(): void
    {
        $response = $this->post('/quick-login', ['role' => 'admin']);
        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();
    }

    public function test_patient_can_view_slots_api(): void
    {
        $patientUser = User::where('role', 'patient')->first();
        $medecin = Medecin::first();

        $response = $this->actingAs($patientUser)->getJson(route('patient.rendez-vous.slots', [
            'medecin_id' => $medecin->id,
            'date' => now()->addDay()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'slots']);
    }

    public function test_secretary_can_update_appointment_status(): void
    {
        $secretaireUser = User::where('role', 'secretaire')->first();
        $rdv = RendezVous::first();

        $response = $this->actingAs($secretaireUser)->post(route('secretaire.rdv.status', $rdv->id), [
            'statut' => 'arrive',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('rendez_vous', [
            'id' => $rdv->id,
            'statut' => 'arrive',
        ]);
    }
}
