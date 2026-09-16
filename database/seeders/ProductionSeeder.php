<?php

namespace Database\Seeders;

use App\Models\Specialite;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Spécialités médicales standard
        $specialites = [
            ['nom' => 'Cardiologie', 'slug' => 'cardiologie', 'icone' => 'heart-pulse', 'description' => 'Maladies du cœur et des vaisseaux sanguins.'],
            ['nom' => 'Gynécologie', 'slug' => 'gynecologie', 'icone' => 'sparkles', 'description' => 'Santé féminine, suivi de grossesse et obstétrique.'],
            ['nom' => 'Pédiatrie', 'slug' => 'pediatrie', 'icone' => 'baby', 'description' => 'Santé et développement de l\'enfant.'],
            ['nom' => 'Dermatologie', 'slug' => 'dermatologie', 'icone' => 'sun', 'description' => 'Affections de la peau et des muqueuses.'],
            ['nom' => 'Neurologie', 'slug' => 'neurologie', 'icone' => 'activity', 'description' => 'Pathologies du système nerveux.'],
            ['nom' => 'Ophtalmologie', 'slug' => 'ophtalmologie', 'icone' => 'eye', 'description' => 'Consultations de la vision et soins oculaires.'],
            ['nom' => 'ORL', 'slug' => 'orl', 'icone' => 'ear', 'description' => 'Troubles de l\'oreille, du nez et de la gorge.'],
            ['nom' => 'Médecine générale', 'slug' => 'medecine-generale', 'icone' => 'stethoscope', 'description' => 'Consultations de médecine générale et suivi global.'],
        ];

        foreach ($specialites as $spe) {
            Specialite::firstOrCreate(['slug' => $spe['slug']], $spe);
        }

        // 2. Compte Administrateur Principal (Modifiable)
        User::firstOrCreate(
            ['email' => 'admin@calebdevs.com'],
            [
                'nom' => 'Admin',
                'prenom' => 'Principal',
                'telephone' => '+33 6 00 00 00 00',
                'role' => 'admin',
                'password' => Hash::make('Admin2026!Securise'),
                'email_verified_at' => now(),
            ]
        );
    }
}
