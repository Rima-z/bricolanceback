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
        Schema::create('commentaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade'); // Clé étrangère vers clients
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade'); // Clé étrangère vers services
            $table->text('texte');
            $table->integer('note');
            $table->date('date');
            $table->boolean('state')->default(0); // Valeur par défaut de 'state' à 0
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commentaires');
    }
};
