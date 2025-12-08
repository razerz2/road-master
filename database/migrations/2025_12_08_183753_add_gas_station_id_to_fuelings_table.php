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
        Schema::table('fuelings', function (Blueprint $table) {
            $table->foreignId('gas_station_id')->nullable()->after('gas_station_name')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fuelings', function (Blueprint $table) {
            $table->dropForeign(['gas_station_id']);
            $table->dropColumn('gas_station_id');
        });
    }
};
