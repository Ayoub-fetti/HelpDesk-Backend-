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
            $table->string('title');
            $table->text('description');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users');
            $table->foreignId('category_id')->constrained('categories');
            $table->enum('priority', ['low', 'average', 'high', 'urgent'])->default('average');
            $table->enum('statut', ['new', 'assigned', 'in_progress', 'on_hold', 'resolved', 'closed', 'reopen'])->default('new');
            $table->timestamp('resolution_date')->nullable();
            $table->text('solution')->nullable();
            $table->float('time_pass_total')->default(0);
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