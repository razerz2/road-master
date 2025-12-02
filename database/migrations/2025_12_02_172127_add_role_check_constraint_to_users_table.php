<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Remove qualquer constraint CHECK problemática na coluna role
     * No SQLite, precisamos recriar a tabela para remover constraints
     */
    public function up(): void
    {
        // No SQLite, não podemos remover constraints CHECK diretamente
        // Precisamos recriar a tabela sem a constraint
        // Desabilitar foreign keys temporariamente
        DB::statement('PRAGMA foreign_keys=OFF');
        
        // Remover tabela temporária se existir (de uma tentativa anterior)
        Schema::dropIfExists('users_temp');
        
        // Primeiro, vamos verificar se há dados e fazer backup
        $users = DB::table('users')->get();
        
        // Criar tabela temporária com a estrutura correta (sem constraint CHECK)
        Schema::create('users_temp', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_full')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('role')->default('condutor');
            $table->boolean('active')->default(true);
            $table->json('preferences')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Copiar dados da tabela antiga para a nova
        foreach ($users as $user) {
            DB::table('users_temp')->insert((array) $user);
        }

        // Remover tabela antiga
        Schema::dropIfExists('users');

        // Renomear tabela temporária
        Schema::rename('users_temp', 'users');
        
        // Reabilitar foreign keys
        DB::statement('PRAGMA foreign_keys=ON');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não há como reverter sem saber qual era a constraint original
        // Esta migration remove constraints CHECK problemáticas
    }
};
