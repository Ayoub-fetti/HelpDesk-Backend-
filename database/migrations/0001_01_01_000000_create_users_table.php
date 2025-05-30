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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('departement')->nullable();
            $table->enum('type_utilisateur', ['administrateur', 'technicien', 'superviseur', 'utilisateur_final'])->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamp('derniere_connexion')->nullable();
            // Technicien specific fields
            $table->string('specialisation')->nullable(); // For technicien
            $table->integer('charge_actuelle')->default(0); // For technicien
            $table->integer('nombre_ticket_resolu')->default(0); // For technicien
            // UtilisateurFinal specific fields
            $table->integer('nombre_ticket_ouvertes')->default(0); // For utilisateur_final
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};