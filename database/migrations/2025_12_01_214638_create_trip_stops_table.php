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
        Schema::create('trip_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('restrict');
            $table->integer('sequence')->comment('Ordem da parada (1, 2, 3, etc.)');
            $table->timestamps();
            
            // Garantir que não haja sequências duplicadas no mesmo percurso
            $table->unique(['trip_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_stops');
    }
};
