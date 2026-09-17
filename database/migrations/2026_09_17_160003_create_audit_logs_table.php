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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->default('Système');
            $table->string('user_role')->default('system');
            $table->string('action'); // POINTAGE_ARRIVEE, STATUT_CHANGE, CREATION_RDV, ANNULATION_RDV, etc.
            $table->text('description');
            $table->string('ip_address')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
