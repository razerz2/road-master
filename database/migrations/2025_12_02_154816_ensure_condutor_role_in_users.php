<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Atualiza dados existentes de 'motorista' para 'condutor' caso ainda existam
     */
    public function up(): void
    {
        // Atualizar todos os usuários com role 'motorista' para 'condutor'
        // Isso garante compatibilidade mesmo se a migration anterior foi removida
        DB::table('users')
            ->where('role', 'motorista')
            ->update(['role' => 'condutor']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não reverter, pois queremos manter 'condutor'
    }
};
