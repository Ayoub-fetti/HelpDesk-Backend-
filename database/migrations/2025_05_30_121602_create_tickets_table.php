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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description');
            $table->foreignId('utilisateur_id')->constrained('users');
            $table->foreignId('technicien_id')->nullable()->constrained('users');
            $table->foreignId('categorie_id')->constrained('categories');
            $table->enum('priorite', ['basse', 'moyenne', 'haute', 'urgente'])->default('moyenne');
            $table->enum('statut', ['nouveau', 'assigné', 'en_cours', 'en_attente', 'résolu', 'fermé', 'rouvert'])->default('nouveau');
            $table->timestamp('date_resolution')->nullable();
            $table->text('solution')->nullable();
            $table->float('temps_passe_total')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};