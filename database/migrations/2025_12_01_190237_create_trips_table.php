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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->foreignId('origin_location_id')->constrained('locations')->onDelete('restrict');
            $table->foreignId('destination_location_id')->constrained('locations')->onDelete('restrict');
            $table->boolean('return_to_origin')->default(false);
            $table->time('departure_time');
            $table->time('return_time')->nullable();
            $table->integer('odometer_start');
            $table->integer('odometer_end');
            $table->integer('km_total');
            $table->text('purpose')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
