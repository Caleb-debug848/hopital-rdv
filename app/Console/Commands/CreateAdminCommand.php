<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminCommand extends Command
{
    protected $signature = 'app:create-admin {email?} {password?}';
    protected $description = 'Créer un compte Administrateur de production';

    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('Adresse email de l\'administrateur', 'admin@calebdevs.com');
        $password = $this->argument('password') ?? $this->secret('Mot de passe de l\'administrateur');

        $nom = $this->ask('Nom', 'Administrateur');
        $prenom = $this->ask('Prénom', 'Principal');
        $telephone = $this->ask('Téléphone', '+33 6 00 00 00 00');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'nom' => $nom,
                'prenom' => $prenom,
                'telephone' => $telephone,
                'role' => 'admin',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $this->info("✓ Compte Administrateur [{$user->email}] configuré avec succès avec le rôle [admin] !");
        return 0;
    }
}
