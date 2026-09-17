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
        Schema::create('parametres', function (Blueprint $table) {
            $table->id();
            $table->string('nom_hopital')->default('Centre Hospitalier Universitaire');
            $table->string('slogan')->nullable()->default('Excellence médicale & Prise en charge humaine');
            $table->string('adresse')->default('Avenue de l\'Hôpital, Douala, Cameroun');
            $table->string('telephone')->default('+237 600 00 00 00');
            $table->string('email_contact')->default('contact@hopital-rdv.cm');
            $table->integer('duree_creneau_defaut')->default(30); // 15, 20, 30, 45, 60 min
            $table->string('heure_ouverture')->default('08:00');
            $table->string('heure_fermeture')->default('18:00');
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
};
