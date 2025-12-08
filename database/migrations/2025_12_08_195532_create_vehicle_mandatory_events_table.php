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
        Schema::create('vehicle_mandatory_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['licenciamento', 'ipva', 'multa']);
            $table->date('due_date'); // data limite
            $table->boolean('notified')->default(false); // já notificou?
            $table->boolean('resolved')->default(false); // pagamento realizado?
            $table->text('description')->nullable(); // informação adicional
            $table->timestamps();
            
            // Índices para melhor performance
            $table->index(['vehicle_id', 'resolved']);
            $table->index(['due_date', 'resolved', 'notified']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_mandatory_events');
    }
};
