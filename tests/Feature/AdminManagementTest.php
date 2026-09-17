<?php

namespace Tests\Feature;

use App\Models\Medecin;
use App\Models\Specialite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Specialite $specialite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'nom' => 'Directeur',
            'prenom' => 'Hôpital',
        ]);

        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class]);
        $this->specialite = Specialite::firstOrCreate(
            ['slug' => 'cardiologie-test'],
            [
                'nom' => 'Cardiologie Test',
                'icone' => 'heart-pulse',
                'is_active' => true,
            ]
        );
    }

    public function test_admin_can_view_doctors_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.medecins.index'));
        $response->assertStatus(200);
        $response->assertSee('Corps Médical');
    }

    public function test_admin_can_create_doctor_successfully()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.medecins.store'), [
            'nom' => 'Dupont',
            'prenom' => 'Bernard',
            'email' => 'dr.dupont@hopital.fr',
            'telephone' => '+33 6 11 22 33 44',
            'specialite_id' => $this->specialite->id,
            'service' => 'Cardiologie Interventionnelle',
            'bureau' => 'Cabinet 204',
            'jours_consultation' => ['lundi', 'mercredi', 'vendredi'],
            'heure_debut' => '08:30',
            'heure_fin' => '16:30',
            'password' => 'MedecinPass2026!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'dr.dupont@hopital.fr',
            'role' => 'medecin',
        ]);

        $this->assertDatabaseHas('medecins', [
            'specialite_id' => $this->specialite->id,
            'bureau' => 'Cabinet 204',
            'statut' => 'actif',
        ]);
    }

    public function test_doctor_creation_fails_without_password()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.medecins.store'), [
            'nom' => 'Dupont',
            'prenom' => 'Bernard',
            'email' => 'dr.dupont@hopital.fr',
            'telephone' => '+33 6 11 22 33 44',
            'specialite_id' => $this->specialite->id,
            'jours_consultation' => ['lundi'],
            'heure_debut' => '08:00',
            'heure_fin' => '16:00',
            // Missing password
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_admin_can_toggle_doctor_status()
    {
        $user = User::factory()->create(['role' => 'medecin']);
        $medecin = Medecin::create([
            'user_id' => $user->id,
            'specialite_id' => $this->specialite->id,
            'jours_consultation' => ['mardi'],
            'heure_debut_defaut' => '08:00:00',
            'heure_fin_defaut' => '16:00:00',
            'statut' => 'actif',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.medecins.toggle', $medecin->id));
        $response->assertRedirect();

        $this->assertEquals('inactif', $medecin->fresh()->statut);
    }

    public function test_admin_can_create_and_update_specialty()
    {
        // Store
        $response = $this->actingAs($this->admin)->post(route('admin.specialites.store'), [
            'nom' => 'Neurologie',
            'description' => 'Service du système nerveux',
            'icone' => 'activity',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('specialites', ['nom' => 'Neurologie']);

        $spe = Specialite::where('nom', 'Neurologie')->first();

        // Update
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.specialites.update', $spe->id), [
            'nom' => 'Neurologie & Neurochirurgie',
            'description' => 'Service neuro avancé',
            'icone' => 'activity',
        ]);

        $responseUpdate->assertRedirect();
        $this->assertEquals('Neurologie & Neurochirurgie', $spe->fresh()->nom);
    }

    public function test_admin_can_create_secretary_user()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'nom' => 'Kone',
            'prenom' => 'Fatou',
            'email' => 'secretaire.fatou@hopital.fr',
            'telephone' => '+33 6 99 88 77 66',
            'role' => 'secretaire',
            'password' => 'SecretPass2026!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'secretaire.fatou@hopital.fr',
            'role' => 'secretaire',
        ]);
    }
}
