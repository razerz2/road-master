<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Module;
use App\Models\UserModulePermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar módulos do sistema
        $modules = [
            ['name' => 'Veículos', 'slug' => 'vehicles'],
            ['name' => 'Locais', 'slug' => 'locations'],
            ['name' => 'Percursos', 'slug' => 'trips'],
            ['name' => 'Abastecimentos', 'slug' => 'fuelings'],
            ['name' => 'Manutenções', 'slug' => 'maintenances'],
            ['name' => 'Relatórios', 'slug' => 'reports'],
            ['name' => 'Usuários', 'slug' => 'users'],
            ['name' => 'Notificações', 'slug' => 'notifications'],
        ];

        foreach ($modules as $moduleData) {
            Module::firstOrCreate(
                ['slug' => $moduleData['slug']],
                ['name' => $moduleData['name']]
            );
        }

        // Seeders de dados básicos do sistema
        $this->call([
            FuelTypeSeeder::class,
            PaymentMethodSeeder::class,
            MaintenanceTypeSeeder::class,
            LocationTypeSeeder::class,
        ]);

        // Criar usuário admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@sckv.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'active' => true,
            ]
        );

        // Criar permissões completas para o admin em todos os módulos
        $allModules = Module::all();
        foreach ($allModules as $module) {
            UserModulePermission::firstOrCreate(
                [
                    'user_id' => $admin->id,
                    'module_id' => $module->id,
                ],
                [
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_delete' => true,
                ]
            );
        }

        $this->command->info('Usuário admin criado com sucesso!');
        $this->command->info('Email: admin@sckv.com');
        $this->command->info('Senha: admin123');
        $this->command->info('Módulos criados: ' . $allModules->count());
    }
}
