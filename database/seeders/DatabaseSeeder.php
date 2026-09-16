<?php

namespace Database\Seeders;

use App\Models\Disponibilite;
use App\Models\ListeAttente;
use App\Models\Medecin;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\RendezVous;
use App\Models\Specialite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Spécialités
        $specialitesData = [
            [
                'nom' => 'Cardiologie',
                'slug' => 'cardiologie',
                'icone' => 'heart-pulse',
                'description' => 'Maladies du cœur et des vaisseaux sanguins, bilan cardiaque et hypertension.',
            ],
            [
                'nom' => 'Gynécologie',
                'slug' => 'gynecologie',
                'icone' => 'sparkles',
                'description' => 'Santé féminine, suivi de grossesse, échographies obstétricales et consultations spécialisées.',
            ],
            [
                'nom' => 'Pédiatrie',
                'slug' => 'pediatrie',
                'icone' => 'baby',
                'description' => 'Développement de l\'enfant, vaccinations, pédiatrie générale et néonatale.',
            ],
            [
                'nom' => 'Dermatologie',
                'slug' => 'dermatologie',
                'icone' => 'sun',
                'description' => 'Affections de la peau, des muqueuses, des ongles et du cuir chevelu.',
            ],
            [
                'nom' => 'Neurologie',
                'slug' => 'neurologie',
                'icone' => 'activity',
                'description' => 'Pathologies du système nerveux central et périphérique, migraines et vertiges.',
            ],
            [
                'nom' => 'Ophtalmologie',
                'slug' => 'ophtalmologie',
                'icone' => 'eye',
                'description' => 'Consultations de la vision, glaucome, cataracte et correction optique.',
            ],
            [
                'nom' => 'ORL',
                'slug' => 'orl',
                'icone' => 'ear',
                'description' => 'Oto-Rhino-Laryngologie : troubles de l\'audition, du nez, de la gorge et de la voix.',
            ],
            [
                'nom' => 'Médecine générale',
                'slug' => 'medecine-generale',
                'icone' => 'stethoscope',
                'description' => 'Consultations de médecine générale, bilans préventifs et suivi global.',
            ],
        ];

        $specialites = [];
        foreach ($specialitesData as $data) {
            $specialites[$data['slug']] = Specialite::create($data);
        }

        // 2. Administrateur
        $admin = User::create([
            'nom' => 'Kouassi',
            'prenom' => 'Michel',
            'email' => 'admin@hopital-rdv.com',
            'telephone' => '+225 07 01 02 03 04',
            'role' => 'admin',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // 3. Secrétaire
        $secretaire = User::create([
            'nom' => 'Bakayoko',
            'prenom' => 'Aminata',
            'email' => 'secretaire@hopital-rdv.com',
            'telephone' => '+225 05 11 22 33 44',
            'role' => 'secretaire',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        // 4. Médecins
        $medecinsList = [
            [
                'user' => [
                    'nom' => 'Koné',
                    'prenom' => 'Jean',
                    'email' => 'dr.kone@hopital-rdv.com',
                    'telephone' => '+225 07 44 55 66 77',
                    'role' => 'medecin',
                    'password' => Hash::make('password123'),
                ],
                'specialite' => 'cardiologie',
                'service' => 'Cardiologie & Soins Intensifs',
                'bureau' => 'Bâtiment B - Bureau 204',
                'biographie' => 'Spécialiste en cardiologie interventionnelle avec plus de 12 ans d\'expérience hospitalière.',
                'jours' => ['lundi', 'mercredi', 'vendredi'],
                'debut' => '08:00:00',
                'fin' => '15:00:00',
            ],
            [
                'user' => [
                    'nom' => 'Touré',
                    'prenom' => 'Fatou',
                    'email' => 'dr.toure@hopital-rdv.com',
                    'telephone' => '+225 07 88 99 00 11',
                    'role' => 'medecin',
                    'password' => Hash::make('password123'),
                ],
                'specialite' => 'gynecologie',
                'service' => 'Gynécologie - Obstétrique',
                'bureau' => 'Pavillon Maternité - Bureau 102',
                'biographie' => 'Gynécologue-obstétricienne spécialisée dans le suivi de grossesse et l\'échographie foetale.',
                'jours' => ['mardi', 'jeudi', 'vendredi'],
                'debut' => '08:30:00',
                'fin' => '16:00:00',
            ],
            [
                'user' => [
                    'nom' => 'Yao',
                    'prenom' => 'Koffi',
                    'email' => 'dr.yao@hopital-rdv.com',
                    'telephone' => '+225 05 77 88 99 00',
                    'role' => 'medecin',
                    'password' => Hash::make('password123'),
                ],
                'specialite' => 'pediatrie',
                'service' => 'Pédiatrie & Urgences Enfants',
                'bureau' => 'Bâtiment A - Bureau 105',
                'biographie' => 'Pédiatre dévoué au suivi du développement infantile et aux vaccinations pédiatriques.',
                'jours' => ['lundi', 'mardi', 'jeudi'],
                'debut' => '08:00:00',
                'fin' => '14:30:00',
            ],
            [
                'user' => [
                    'nom' => 'Bamba',
                    'prenom' => 'Salimata',
                    'email' => 'dr.bamba@hopital-rdv.com',
                    'telephone' => '+225 01 22 33 44 55',
                    'role' => 'medecin',
                    'password' => Hash::make('password123'),
                ],
                'specialite' => 'medecine-generale',
                'service' => 'Consultations Générales & Prévention',
                'bureau' => 'Bâtiment Central - Bureau 12',
                'biographie' => 'Médecin généraliste avec approche holistique et orientée vers la prévention de santé.',
                'jours' => ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'],
                'debut' => '08:00:00',
                'fin' => '17:00:00',
            ],
        ];

        $medecins = [];
        foreach ($medecinsList as $m) {
            $user = User::create(array_merge($m['user'], ['email_verified_at' => now()]));
            $medecin = Medecin::create([
                'user_id' => $user->id,
                'specialite_id' => $specialites[$m['specialite']]->id,
                'titre' => 'Dr',
                'service' => $m['service'],
                'bureau' => $m['bureau'],
                'biographie' => $m['biographie'],
                'jours_consultation' => $m['jours'],
                'heure_debut_defaut' => $m['debut'],
                'heure_fin_defaut' => $m['fin'],
                'duree_consultation' => 30,
                'statut' => 'actif',
            ]);

            // Création des disponibilités récurrentes
            foreach ($m['jours'] as $jour) {
                Disponibilite::create([
                    'medecin_id' => $medecin->id,
                    'jour_semaine' => $jour,
                    'heure_debut' => $m['debut'],
                    'heure_fin' => $m['fin'],
                    'duree_creneau' => 30,
                    'is_active' => true,
                ]);
            }

            $medecins[] = $medecin;
        }

        // 5. Patients
        $patientsData = [
            [
                'user' => [
                    'nom' => 'Kouamé',
                    'prenom' => 'Awa',
                    'email' => 'awa.kouame@gmail.com',
                    'telephone' => '+225 07 10 20 30 40',
                    'role' => 'patient',
                    'password' => Hash::make('password123'),
                ],
                'date_naissance' => '1992-05-14',
                'sexe' => 'F',
                'contact_urgence' => '+225 07 99 88 77 66 (Époux)',
                'adresse' => 'Abidjan Cocody, Angré 8ème Tranche',
            ],
            [
                'user' => [
                    'nom' => 'Traoré',
                    'prenom' => 'Marc',
                    'email' => 'marc.traore@gmail.com',
                    'telephone' => '+225 05 22 33 44 55',
                    'role' => 'patient',
                    'password' => Hash::make('password123'),
                ],
                'date_naissance' => '1985-11-20',
                'sexe' => 'M',
                'contact_urgence' => '+225 05 44 33 22 11 (Frère)',
                'adresse' => 'Abidjan Yopougon, Maroc',
            ],
            [
                'user' => [
                    'nom' => 'Diallo',
                    'prenom' => 'Mariam',
                    'email' => 'mariam.diallo@gmail.com',
                    'telephone' => '+225 01 55 66 77 88',
                    'role' => 'patient',
                    'password' => Hash::make('password123'),
                ],
                'date_naissance' => '1998-03-08',
                'sexe' => 'F',
                'contact_urgence' => '+225 01 88 77 66 55 (Mère)',
                'adresse' => 'Abidjan Marcory, Zone 4',
            ],
            [
                'user' => [
                    'nom' => 'Gnépa',
                    'prenom' => 'Éric',
                    'email' => 'eric.gnepa@gmail.com',
                    'telephone' => '+225 07 33 22 11 00',
                    'role' => 'patient',
                    'password' => Hash::make('password123'),
                ],
                'date_naissance' => '1979-08-30',
                'sexe' => 'M',
                'contact_urgence' => '+225 07 00 11 22 33 (Épouse)',
                'adresse' => 'Abidjan Plateau',
            ],
        ];

        $patients = [];
        foreach ($patientsData as $p) {
            $u = User::create(array_merge($p['user'], ['email_verified_at' => now()]));
            $patients[] = Patient::create([
                'user_id' => $u->id,
                'date_naissance' => $p['date_naissance'],
                'sexe' => $p['sexe'],
                'contact_urgence' => $p['contact_urgence'],
                'adresse' => $p['adresse'],
            ]);
        }

        // 6. Rendez-vous de test pour aujourd'hui et les prochains jours
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $rdvSamples = [
            // Aujourd'hui
            [
                'patient_id' => $patients[0]->id,
                'medecin_id' => $medecins[0]->id, // Dr Koné Cardio
                'specialite_id' => $medecins[0]->specialite_id,
                'date_rdv' => $today->toDateString(),
                'heure_rdv' => '08:30:00',
                'statut' => 'arrive',
                'motif' => 'Contrôle de tension artérielle & suivi traitement',
                'date_arrivee' => $today->copy()->setTime(8, 25),
            ],
            [
                'patient_id' => $patients[1]->id,
                'medecin_id' => $medecins[0]->id, // Dr Koné Cardio
                'specialite_id' => $medecins[0]->specialite_id,
                'date_rdv' => $today->toDateString(),
                'heure_rdv' => '09:00:00',
                'statut' => 'confirme',
                'motif' => 'Douleurs thoraciques d\'effort',
            ],
            [
                'patient_id' => $patients[2]->id,
                'medecin_id' => $medecins[1]->id, // Dr Touré Gynéco
                'specialite_id' => $medecins[1]->specialite_id,
                'date_rdv' => $today->toDateString(),
                'heure_rdv' => '10:00:00',
                'statut' => 'en_attente',
                'motif' => 'Échographie de contrôle 2ème trimestre',
            ],
            [
                'patient_id' => $patients[3]->id,
                'medecin_id' => $medecins[3]->id, // Dr Bamba MG
                'specialite_id' => $medecins[3]->specialite_id,
                'date_rdv' => $today->toDateString(),
                'heure_rdv' => '08:00:00',
                'statut' => 'termine',
                'motif' => 'Bilan annuel de santé',
                'date_arrivee' => $today->copy()->setTime(7, 50),
                'date_effectue' => $today->copy()->setTime(8, 30),
            ],
            // Demain
            [
                'patient_id' => $patients[0]->id,
                'medecin_id' => $medecins[1]->id, // Dr Touré Gynéco
                'specialite_id' => $medecins[1]->specialite_id,
                'date_rdv' => $tomorrow->toDateString(),
                'heure_rdv' => '14:00:00',
                'statut' => 'confirme',
                'motif' => 'Consultation de routine',
            ],
            [
                'patient_id' => $patients[2]->id,
                'medecin_id' => $medecins[2]->id, // Dr Yao Pédiatrie
                'specialite_id' => $medecins[2]->specialite_id,
                'date_rdv' => $tomorrow->toDateString(),
                'heure_rdv' => '09:30:00',
                'statut' => 'confirme',
                'motif' => 'Vaccin rappel 11 mois bébé',
            ],
            // Rendez-vous annulé pour test de liste d'attente
            [
                'patient_id' => $patients[3]->id,
                'medecin_id' => $medecins[0]->id,
                'specialite_id' => $medecins[0]->specialite_id,
                'date_rdv' => $today->toDateString(),
                'heure_rdv' => '11:00:00',
                'statut' => 'annule',
                'motif' => 'Consultation cardiologique',
                'annule_par' => 'patient',
                'notes_annulation' => 'Empêchement professionnel imprévu.',
            ],
        ];

        foreach ($rdvSamples as $rdvData) {
            $rdv = RendezVous::create($rdvData);

            // Notification pour le patient
            Notification::create([
                'user_id' => $rdv->patient->user_id,
                'type' => 'confirmation',
                'titre' => 'Rendez-vous ' . $rdv->reference_rdv,
                'message' => 'Votre rendez-vous avec le ' . $rdv->medecin->nom_complet . ' est prévu le ' . Carbon::parse($rdv->date_rdv)->format('d/m/Y') . ' à ' . substr($rdv->heure_rdv, 0, 5) . '.',
                'lien' => '/patient/rendez-vous/' . $rdv->id,
                'lu' => false,
            ]);
        }

        // 7. Liste d'attente d'exemple
        ListeAttente::create([
            'patient_id' => $patients[1]->id,
            'medecin_id' => $medecins[0]->id,
            'specialite_id' => $medecins[0]->specialite_id,
            'date_souhaitee' => $today->toDateString(),
            'plage_horaire' => 'matin',
            'statut' => 'en_attente',
        ]);
    }
}
