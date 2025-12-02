<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Module;
use App\Models\UserModulePermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar todos os módulos para dar permissões aos motoristas
        $modules = Module::all();

        // Buscar veículos disponíveis (assumindo que temos 8 veículos)
        $vehicles = Vehicle::all();

        // Definir 4 motoristas com suas configurações
        $drivers = [
            [
                'name' => 'João Silva',
                'name_full' => 'João da Silva Santos',
                'email' => 'joao.silva@sckv.com',
                'password' => 'joao123',
                'role' => 'condutor',
                'active' => true,
                'vehicle_ids' => [1, 2], // VW Gol e Toyota Corolla
            ],
            [
                'name' => 'Maria Oliveira',
                'name_full' => 'Maria de Oliveira Costa',
                'email' => 'maria.oliveira@sckv.com',
                'password' => 'maria123',
                'role' => 'condutor',
                'active' => true,
                'vehicle_ids' => [3, 4], // Ford Ranger e Chevrolet Onix
            ],
            [
                'name' => 'Pedro Santos',
                'name_full' => 'Pedro Henrique Santos',
                'email' => 'pedro.santos@sckv.com',
                'password' => 'pedro123',
                'role' => 'condutor',
                'active' => true,
                'vehicle_ids' => [5, 6, 7], // Fiat Uno, Honda Civic e Renault Duster
            ],
            [
                'name' => 'Ana Costa',
                'name_full' => 'Ana Paula Costa Lima',
                'email' => 'ana.costa@sckv.com',
                'password' => 'ana123',
                'role' => 'condutor',
                'active' => true,
                'vehicle_ids' => [8], // Hyundai HB20
            ],
        ];

        foreach ($drivers as $driverData) {
            $vehicleIds = $driverData['vehicle_ids'];
            unset($driverData['vehicle_ids']);
            $password = $driverData['password'];
            $driverData['password'] = Hash::make($password);

            // Criar usuário
            $user = User::create($driverData);

            // Associar veículos
            if (!empty($vehicleIds)) {
                $userVehicles = Vehicle::whereIn('id', $vehicleIds)->get();
                $user->vehicles()->sync($userVehicles->pluck('id'));
            }

            // Dar permissões básicas em todos os módulos (view apenas)
            foreach ($modules as $module) {
                UserModulePermission::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'module_id' => $module->id,
                    ],
                    [
                        'can_view' => true,
                        'can_create' => false,
                        'can_edit' => false,
                        'can_delete' => false,
                    ]
                );
            }

            // Informar credenciais
            $vehiclePlates = Vehicle::whereIn('id', $vehicleIds)->pluck('plate')->implode(', ');
            $this->command->info("Motorista criado: {$user->name}");
            $this->command->info("  Email: {$user->email}");
            $this->command->info("  Senha: {$password}");
            $this->command->info("  Veículos: {$vehiclePlates}");
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  RESUMO DE CREDENCIAIS DOS MOTORISTAS');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('1. João Silva');
        $this->command->info('   Email: joao.silva@sckv.com');
        $this->command->info('   Senha: joao123');
        $this->command->info('   Veículos: VW Gol (ABC-1234), Toyota Corolla (XYZ-5678)');
        $this->command->info('');
        $this->command->info('2. Maria Oliveira');
        $this->command->info('   Email: maria.oliveira@sckv.com');
        $this->command->info('   Senha: maria123');
        $this->command->info('   Veículos: Ford Ranger (DEF-9012), Chevrolet Onix (GHI-3456)');
        $this->command->info('');
        $this->command->info('3. Pedro Santos');
        $this->command->info('   Email: pedro.santos@sckv.com');
        $this->command->info('   Senha: pedro123');
        $this->command->info('   Veículos: Fiat Uno (JKL-7890), Honda Civic (MNO-2468), Renault Duster (PQR-1357)');
        $this->command->info('');
        $this->command->info('4. Ana Costa');
        $this->command->info('   Email: ana.costa@sckv.com');
        $this->command->info('   Senha: ana123');
        $this->command->info('   Veículos: Hyundai HB20 (STU-8024)');
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
    }
}
