<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medecins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('specialite_id')->constrained('specialites')->restrictOnDelete();
            $table->string('titre', 50)->default('Dr');
            $table->string('service', 100)->nullable();
            $table->string('bureau', 50)->nullable();
            $table->text('biographie')->nullable();
            $table->json('jours_consultation')->nullable();
            $table->time('heure_debut_defaut')->default('08:00:00');
            $table->time('heure_fin_defaut')->default('16:00:00');
            $table->unsignedInteger('duree_consultation')->default(30);
            $table->string('statut', 30)->default('actif'); // actif, en_conge, inactif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medecins');
    }
};
