<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendez_vous', function (Blueprint $table) {
            $table->id();
            $table->string('reference_rdv', 50)->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('medecin_id')->constrained('medecins')->cascadeOnDelete();
            $table->foreignId('specialite_id')->constrained('specialites')->restrictOnDelete();
            $table->date('date_rdv');
            $table->time('heure_rdv');
            $table->time('heure_fin_rdv')->nullable();
            $table->string('statut', 30)->default('en_attente'); // en_attente, confirme, arrive, termine, annule, absent
            $table->string('motif', 255)->nullable();
            $table->text('notes_annulation')->nullable();
            $table->string('annule_par', 30)->nullable();
            $table->timestamp('date_arrivee')->nullable();
            $table->timestamp('date_effectue')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendez_vous');
    }
};
