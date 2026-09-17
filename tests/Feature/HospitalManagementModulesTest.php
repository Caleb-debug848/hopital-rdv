<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Cabinet;
use App\Models\Medecin;
use App\Models\Parametre;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Specialite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalManagementModulesTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $medecinUser;
    protected Medecin $medecin;
    protected User $patientUser;
    protected Patient $patient;
    protected Specialite $specialite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'nom' => 'Directeur',
            'prenom' => 'Admin',
            'role' => 'admin',
        ]);

        $this->specialite = Specialite::firstOrCreate(
            ['slug' => 'cardiologie-test'],
            [
                'nom' => 'Cardiologie Test',
                'icone' => 'heart-pulse',
                'is_active' => true,
            ]
        );

        $this->medecinUser = User::factory()->create([
            'nom' => 'Kamga',
            'prenom' => 'Paul',
            'role' => 'medecin',
        ]);

        $this->medecin = Medecin::create([
            'user_id' => $this->medecinUser->id,
            'specialite_id' => $this->specialite->id,
            'titre' => 'Dr.',
            'service' => 'Cardiologie',
            'bureau' => 'Cabinet 101',
            'jours_consultation' => ['lundi', 'mardi', 'mercredi'],
            'heure_debut_defaut' => '08:00',
            'heure_fin_defaut' => '16:00',
            'duree_consultation' => 30,
            'statut' => 'actif',
        ]);

        $this->patientUser = User::factory()->create([
            'nom' => 'Ngo',
            'prenom' => 'Marie',
            'role' => 'patient',
        ]);

        $this->patient = Patient::create([
            'user_id' => $this->patientUser->id,
            'sexe' => 'Féminin',
        ]);
    }

    /**
     * Test Module 5 : Dossier Patient Unique et Centralisé
     */
    public function test_admin_can_view_centralized_patient_file(): void
    {
        $rdv = RendezVous::create([
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'specialite_id' => $this->specialite->id,
            'date_rdv' => now()->toDateString(),
            'heure_rdv' => '09:00',
            'statut' => 'confirme',
            'motif' => 'Contrôle tension artérielle',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.patients.show', $this->patient->id));

        $response->assertStatus(200);
        $response->assertSee('Dossier Patient Unique');
        $response->assertSee('Marie Ngo');
        $response->assertSee('Paul Kamga');
        $response->assertSee($rdv->reference_rdv);
    }

    /**
     * Test Module 2 : Centre de Rapports et Export CSV (1 Clic)
     */
    public function test_admin_can_export_consultations_csv(): void
    {
        $rdv = RendezVous::create([
            'patient_id' => $this->patient->id,
            'medecin_id' => $this->medecin->id,
            'specialite_id' => $this->specialite->id,
            'date_rdv' => now()->toDateString(),
            'heure_rdv' => '10:00',
            'statut' => 'termine',
            'motif' => 'Bilan annuel',
        ]);

        // 1. Test Export Excel Pro Stylé (.XLS)
        $responseExcel = $this->actingAs($this->adminUser)->get(route('admin.statistiques.export', ['periode' => 'ce_mois', 'format' => 'excel']));
        $responseExcel->assertStatus(200);
        $responseExcel->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename="bilan-activite-hospitaliere-', $responseExcel->headers->get('Content-Disposition'));
        $this->assertStringContainsString('BILAN OFFICIEL', $responseExcel->getContent());
        $this->assertStringContainsString($rdv->reference_rdv, $responseExcel->getContent());

        // 2. Test Export CSV Brut
        $responseCsv = $this->actingAs($this->adminUser)->get(route('admin.statistiques.export', ['periode' => 'ce_mois', 'format' => 'csv']));
        $responseCsv->assertStatus(200);
        $responseCsv->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename="rapport-activite-hospitaliere-', $responseCsv->headers->get('Content-Disposition'));

        // 3. Test Rapport Direction A4 PDF
        $responsePdf = $this->actingAs($this->adminUser)->get(route('admin.statistiques.rapport-pdf', ['periode' => 'ce_mois']));
        $responsePdf->assertStatus(200);
        $responsePdf->assertSee('Rapport Exécutif', false);
        $responsePdf->assertSee($rdv->reference_rdv);
    }

    /**
     * Test Module 2 : Page Statistiques avec Affluence Horaire
     */
    public function test_admin_can_view_statistiques_with_peak_hours(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.statistiques'));

        $response->assertStatus(200);
        $response->assertSee('Centre de Rapports');
        $response->assertSee('Affluence');
        $response->assertSee('Pointe');
        $response->assertSee('Absentéisme');
    }


    /**
     * Test Module 4 : Journal d'Audit et Traçabilité
     */
    public function test_admin_can_view_audit_logs_and_events_are_tracked(): void
    {
        \App\Services\AuditLogger::log(
            'POINTAGE_ARRIVEE',
            'Secrétaire Marie a pointé l\'arrivée de Marie Ngo',
            ['patient_id' => $this->patient->id],
            $this->adminUser
        );

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'POINTAGE_ARRIVEE',
            'user_name' => $this->adminUser->full_name,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('admin.audit_logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Pointages', false);
        $response->assertSee('Marie Ngo', false);
    }



    /**
     * Test Module 3 : Paramètres Généraux de l'Établissement
     */
    public function test_admin_can_view_and_update_hospital_settings(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.parametres.index'));
        $response->assertStatus(200);
        $response->assertSee('Paramètres Généraux de l\'Établissement', false);

        $updateResponse = $this->actingAs($this->adminUser)->post(route('admin.parametres.update'), [
            'nom_hopital' => 'Clinique Métropolitaine Moderne',
            'slogan' => 'Excellence et soins de qualité',
            'adresse' => 'Quartier Administratif, Douala',
            'telephone' => '+237 699 11 22 33',
            'email_contact' => 'direction@clinique-moderne.cm',
            'duree_creneau_defaut' => 45,
            'heure_ouverture' => '07:30',
            'heure_fermeture' => '19:00',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('parametres', [
            'nom_hopital' => 'Clinique Métropolitaine Moderne',
            'duree_creneau_defaut' => 45,
        ]);
    }

    /**
     * Test Module 1 : Gestion des Cabinets & Salles Médicales
     */
    public function test_admin_can_create_and_manage_cabinets(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.cabinets.store'), [
            'nom' => 'Cabinet 204 - Pneumologie',
            'batiment' => 'Aile Ouest - Spécialités',
            'etage' => '2ème Étage',
            'capacite' => 2,
            'equipements' => 'Spiromètre, Stéthoscope',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cabinets', [
            'nom' => 'Cabinet 204 - Pneumologie',
            'batiment' => 'Aile Ouest - Spécialités',
        ]);

        $indexResponse = $this->actingAs($this->adminUser)->get(route('admin.cabinets.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Cabinet 204 - Pneumologie');
    }

    /**
     * Test Module 6 : Supervision des Rappels et Déclenchement Manuel
     */
    public function test_admin_can_view_reminders_supervision(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.rappels.index'));

        $response->assertStatus(200);
        $response->assertSee('Supervision des Rappels');
        $response->assertSee('CRON OPÉRATIONNEL');
    }

}
