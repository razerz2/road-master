<?php

namespace Database\Seeders;

use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\MaintenanceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = Vehicle::all();
        $maintenanceTypes = MaintenanceType::where('active', true)->get();

        if ($vehicles->isEmpty() || $maintenanceTypes->isEmpty()) {
            $this->command->error('É necessário ter veículos e tipos de manutenção cadastrados!');
            return;
        }

        // Mapear tipos de manutenção por slug
        $typeMap = [];
        foreach ($maintenanceTypes as $type) {
            $typeMap[$type->slug] = $type;
        }

        // Intervalos de manutenção (em KM)
        $maintenanceIntervals = [
            'troca_oleo' => 10000,      // A cada 10.000 km
            'revisao' => 20000,         // A cada 20.000 km
            'pneu' => 40000,            // A cada 40.000 km (ou por desgaste)
            'freio' => 30000,           // A cada 30.000 km
            'suspensao' => 50000,       // A cada 50.000 km
        ];

        // Custos médios por tipo de manutenção
        $maintenanceCosts = [
            'troca_oleo' => ['min' => 150, 'max' => 400],      // R$ 150 a R$ 400
            'revisao' => ['min' => 500, 'max' => 1500],        // R$ 500 a R$ 1.500
            'pneu' => ['min' => 800, 'max' => 2500],           // R$ 800 a R$ 2.500
            'freio' => ['min' => 300, 'max' => 1200],          // R$ 300 a R$ 1.200
            'suspensao' => ['min' => 600, 'max' => 2000],      // R$ 600 a R$ 2.000
            'outro' => ['min' => 200, 'max' => 1000],          // R$ 200 a R$ 1.000
        ];

        // Descrições por tipo
        $descriptions = [
            'troca_oleo' => [
                'Troca de óleo do motor e filtro de óleo',
                'Troca de óleo sintético e filtro',
                'Troca de óleo e filtros (óleo e ar)',
                'Revisão de óleo e troca de filtro',
            ],
            'revisao' => [
                'Revisão completa do veículo',
                'Revisão periódica de 20.000 km',
                'Revisão geral preventiva',
                'Revisão técnica completa',
                'Revisão de rotina',
            ],
            'pneu' => [
                'Troca de 4 pneus',
                'Troca de pneus dianteiros',
                'Troca de pneus traseiros',
                'Troca de pneu traseiro direito',
                'Troca de pneu dianteiro esquerdo',
                'Balanceamento e alinhamento',
                'Troca de pneus e balanceamento',
            ],
            'freio' => [
                'Troca de pastilhas de freio dianteiras',
                'Troca de pastilhas de freio traseiras',
                'Troca completa do sistema de freios',
                'Revisão e troca de pastilhas de freio',
                'Troca de discos e pastilhas',
            ],
            'suspensao' => [
                'Revisão do sistema de suspensão',
                'Troca de amortecedores dianteiros',
                'Troca de amortecedores traseiros',
                'Troca de amortecedores e molas',
                'Revisão completa da suspensão',
            ],
            'outro' => [
                'Troca de correia dentada',
                'Troca de filtro de ar',
                'Troca de filtro de combustível',
                'Limpeza de bicos injetores',
                'Troca de velas',
                'Troca de bateria',
                'Reparo elétrico',
            ],
        ];

        // Fornecedores
        $providers = [
            'Oficina Mecânica Central',
            'Auto Center São Paulo',
            'Oficina Especializada',
            'Concessionária Autorizada',
            'Auto Serviço Express',
            'Mecânica Moderna',
            'Oficina Confiança',
            'Auto Reparo',
            'Mecânica Premium',
            'Serviço Automotivo',
        ];

        $totalMaintenances = 0;

        foreach ($vehicles as $vehicle) {
            // Obter percursos do veículo para basear manutenções
            $trips = Trip::where('vehicle_id', $vehicle->id)
                ->orderBy('date')
                ->orderBy('odometer_start')
                ->get();

            $kmInicial = $vehicle->km_inicial ?? 0;
            $currentOdometer = $kmInicial;

            // Rastrear última manutenção de cada tipo
            $lastMaintenance = [
                'troca_oleo' => ['km' => $kmInicial, 'date' => Carbon::now()->subMonths(6)],
                'revisao' => ['km' => $kmInicial, 'date' => Carbon::now()->subMonths(6)],
                'pneu' => ['km' => $kmInicial, 'date' => Carbon::now()->subMonths(6)],
                'freio' => ['km' => $kmInicial, 'date' => Carbon::now()->subMonths(6)],
                'suspensao' => ['km' => $kmInicial, 'date' => Carbon::now()->subMonths(6)],
                'outro' => ['km' => $kmInicial, 'date' => Carbon::now()->subMonths(6)],
            ];

            // Criar manutenções periódicas baseadas em KM
            foreach ($trips as $trip) {
                $currentOdometer = max($currentOdometer, $trip->odometer_end);
                $tripDate = Carbon::parse($trip->date);

                // Verificar cada tipo de manutenção
                foreach ($maintenanceIntervals as $type => $interval) {
                    $kmSinceLastMaintenance = $currentOdometer - $lastMaintenance[$type]['km'];
                    
                    // Se passou do intervalo, criar manutenção
                    if ($kmSinceLastMaintenance >= $interval) {
                        // Tipo de manutenção
                        $maintenanceType = $typeMap[$type] ?? $typeMap['outro'] ?? null;
                        
                        if (!$maintenanceType) {
                            continue;
                        }

                        // Descrição
                        $typeDescriptions = $descriptions[$type] ?? ['Manutenção realizada'];
                        $description = $typeDescriptions[array_rand($typeDescriptions)];

                        // Custo
                        $costRange = $maintenanceCosts[$type] ?? $maintenanceCosts['outro'];
                        $cost = rand($costRange['min'] * 100, $costRange['max'] * 100) / 100;

                        // Fornecedor
                        $provider = $providers[array_rand($providers)];

                        // Data da manutenção (pouco antes ou depois da viagem)
                        $maintenanceDate = $tripDate->copy()->subDays(rand(0, 7));

                        // Odômetro da manutenção (no momento da viagem)
                        $maintenanceOdometer = $trip->odometer_end;

                        // Próxima manutenção
                        $nextDueOdometer = $maintenanceOdometer + $interval;
                        $nextDueDate = $maintenanceDate->copy()->addMonths(6); // 6 meses ou próximo intervalo

                        // Criar manutenção
                        DB::transaction(function () use (
                            $vehicle,
                            $maintenanceType,
                            $type,
                            $maintenanceDate,
                            $maintenanceOdometer,
                            $description,
                            $provider,
                            $cost,
                            $nextDueDate,
                            $nextDueOdometer,
                            &$lastMaintenance,
                            &$totalMaintenances
                        ) {
                            Maintenance::create([
                                'vehicle_id' => $vehicle->id,
                                'date' => $maintenanceDate->format('Y-m-d'),
                                'odometer' => $maintenanceOdometer,
                                'type' => $type,
                                'maintenance_type_id' => $maintenanceType->id,
                                'description' => $description,
                                'provider' => $provider,
                                'cost' => $cost,
                                'next_due_date' => $nextDueDate->format('Y-m-d'),
                                'next_due_odometer' => $nextDueOdometer,
                                'notes' => rand(1, 5) == 1 ? 'Manutenção preventiva realizada conforme cronograma' : null,
                            ]);

                            // Atualizar última manutenção deste tipo
                            $lastMaintenance[$type] = [
                                'km' => $maintenanceOdometer,
                                'date' => $maintenanceDate,
                            ];

                            $totalMaintenances++;
                        });
                    }
                }
            }

            // Criar algumas manutenções adicionais (corretivas/preventivas extras)
            // Manutenções menores entre as periódicas
            $additionalMaintenances = rand(2, 5);
            
            for ($i = 0; $i < $additionalMaintenances; $i++) {
                // Escolher tipo aleatório
                $additionalTypes = ['troca_oleo', 'freio', 'outro'];
                $randomType = $additionalTypes[array_rand($additionalTypes)];
                
                $maintenanceType = $typeMap[$randomType] ?? $typeMap['outro'] ?? null;
                
                if (!$maintenanceType) {
                    continue;
                }

                // Data aleatória
                $maintenanceDate = Carbon::now()->subMonths(rand(1, 5))->subDays(rand(0, 30));
                
                // Odômetro baseado em algum percurso
                if ($trips->isNotEmpty()) {
                    $randomTrip = $trips->random();
                    $maintenanceOdometer = $randomTrip->odometer_end - rand(100, 500);
                } else {
                    $maintenanceOdometer = $kmInicial + rand(5000, 15000);
                }

                // Verificar se não é muito próximo de outra manutenção do mesmo tipo
                if (isset($lastMaintenance[$randomType]) && abs($maintenanceOdometer - $lastMaintenance[$randomType]['km']) < 2000) {
                    continue;
                }

                // Descrição e custo
                $typeDescriptions = $descriptions[$randomType] ?? ['Manutenção realizada'];
                $description = $typeDescriptions[array_rand($typeDescriptions)];
                $costRange = $maintenanceCosts[$randomType] ?? $maintenanceCosts['outro'];
                $cost = rand($costRange['min'] * 100, $costRange['max'] * 100) / 100;
                $provider = $providers[array_rand($providers)];

                // Próxima manutenção
                $interval = $maintenanceIntervals[$randomType] ?? 10000;
                $nextDueOdometer = $maintenanceOdometer + $interval;
                $nextDueDate = $maintenanceDate->copy()->addMonths(3);

                DB::transaction(function () use (
                    $vehicle,
                    $maintenanceType,
                    $randomType,
                    $maintenanceDate,
                    $maintenanceOdometer,
                    $description,
                    $provider,
                    $cost,
                    $nextDueDate,
                    $nextDueOdometer,
                    &$totalMaintenances
                ) {
                    Maintenance::create([
                        'vehicle_id' => $vehicle->id,
                        'date' => $maintenanceDate->format('Y-m-d'),
                        'odometer' => $maintenanceOdometer,
                        'type' => $randomType,
                        'maintenance_type_id' => $maintenanceType->id,
                        'description' => $description,
                        'provider' => $provider,
                        'cost' => $cost,
                        'next_due_date' => $nextDueDate->format('Y-m-d'),
                        'next_due_odometer' => $nextDueOdometer,
                    ]);

                    $totalMaintenances++;
                });
            }
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  MANUTENÇÕES CRIADAS COM SUCESSO!');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info("Total de manutenções criadas: {$totalMaintenances}");
        $this->command->info('');
        
        // Estatísticas por veículo
        foreach ($vehicles as $vehicle) {
            $vehicleMaintenances = Maintenance::where('vehicle_id', $vehicle->id)->get();
            $totalCost = $vehicleMaintenances->sum('cost');
            
            $this->command->info("{$vehicle->name} ({$vehicle->plate}):");
            $this->command->info("  - Manutenções: {$vehicleMaintenances->count()}");
            $this->command->info("  - Custo total: R$ " . number_format($totalCost, 2, ',', '.'));
            
            // Por tipo
            $byType = $vehicleMaintenances->groupBy('type');
            foreach ($byType as $type => $maintenances) {
                $typeCost = $maintenances->sum('cost');
                $this->command->info("    * " . ucfirst(str_replace('_', ' ', $type)) . ": {$maintenances->count()} (R$ " . number_format($typeCost, 2, ',', '.') . ")");
            }
            $this->command->info('');
        }
    }
}
