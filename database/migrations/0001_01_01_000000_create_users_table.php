<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // No SQLite, precisamos criar a tabela com constraint CHECK diretamente via SQL
        // pois o Schema Builder do Laravel não suporta CHECK constraints
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                name_full VARCHAR(255),
                email VARCHAR(255) NOT NULL UNIQUE,
                email_verified_at DATETIME,
                password VARCHAR(255) NOT NULL,
                avatar VARCHAR(255),
                role VARCHAR(255) NOT NULL DEFAULT 'condutor' CHECK(role IN ('admin', 'condutor')),
                active TINYINT(1) NOT NULL DEFAULT 1,
                preferences TEXT,
                remember_token VARCHAR(100),
                created_at DATETIME,
                updated_at DATETIME
            )");
        } else {
            // Para outros bancos de dados, usar Schema Builder
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('name_full')->nullable();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('avatar')->nullable();
                $table->string('role')->default('condutor'); // SQLite não suporta enum, usar string
                $table->boolean('active')->default(true);
                $table->json('preferences')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
            
            // Adicionar constraint CHECK para outros bancos (PostgreSQL, MySQL 8.0+)
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'condutor'))");
            }
        }

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
