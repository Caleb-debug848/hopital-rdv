<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cabinets', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // ex: "Cabinet 104"
            $table->string('batiment')->default('Bâtiment Principal'); // ex: "Bâtiment A - Consultations Externes"
            $table->string('etage')->default('Rez-de-chaussée'); // ex: "1er Étage"
            $table->integer('capacite')->default(1);
            $table->string('equipements')->nullable();
            $table->boolean('is_actif')->default(true);
            $table->timestamps();
        });

        // Ajouter cabinet_id facultatif sur medecins si la colonne n'existe pas
        if (Schema::hasTable('medecins') && !Schema::hasColumn('medecins', 'cabinet_id')) {
            Schema::table('medecins', function (Blueprint $table) {
                $table->foreignId('cabinet_id')->nullable()->after('specialite_id')->constrained('cabinets')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('medecins') && Schema::hasColumn('medecins', 'cabinet_id')) {
            Schema::table('medecins', function (Blueprint $table) {
                $table->dropConstrainedForeignId('cabinet_id');
            });
        }
        Schema::dropIfExists('cabinets');
    }
};
