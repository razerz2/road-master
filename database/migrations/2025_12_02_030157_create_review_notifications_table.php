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
        Schema::create('review_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('review_type'); // troca_oleo, revisao_manutencao, lavagem, pneu, freio, suspensao, etc
            $table->string('name')->nullable(); // Nome personalizado para a revisão
            $table->integer('current_km')->default(0); // KM atual do veículo quando foi configurado
            $table->integer('notification_km'); // KM onde será disparada a notificação
            $table->integer('last_notified_km')->nullable(); // Último KM onde foi notificado (evita duplicatas)
            $table->boolean('active')->default(true); // Se a notificação está ativa
            $table->text('description')->nullable(); // Descrição adicional
            $table->timestamps();
            
            // Índices para melhor performance
            $table->index(['vehicle_id', 'active']);
            $table->index(['notification_km', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_notifications');
    }
};
