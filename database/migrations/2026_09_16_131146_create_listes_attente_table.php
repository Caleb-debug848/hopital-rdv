<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listes_attente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('medecin_id')->constrained('medecins')->cascadeOnDelete();
            $table->foreignId('specialite_id')->constrained('specialites')->restrictOnDelete();
            $table->date('date_souhaitee');
            $table->string('plage_horaire', 50)->default('toute_la_journee'); // matin, apres_midi, toute_la_journee
            $table->string('statut', 30)->default('en_attente'); // en_attente, notifie, converti, expire
            $table->timestamp('date_notification')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listes_attente');
    }
};
