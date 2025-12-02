<?php

namespace Database\Seeders;

use App\Models\Trip;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar dados
        $vehicles = Vehicle::all();
        $drivers = User::where('role', 'condutor')->get();
        $locations = Location::all();
        $admin = User::where('role', 'admin')->first();

        if ($vehicles->isEmpty() || $drivers->isEmpty() || $locations->count() < 2) {
            $this->command->error('É necessário ter veículos, motoristas e pelo menos 2 locais cadastrados!');
            return;
        }

        // Propósitos de viagem
        $purposes = [
            'Entrega de mercadorias',
            'Atendimento a cliente',
            'Visita técnica',
            'Reunião comercial',
            'Coleta de materiais',
            'Transporte de funcionários',
            'Serviço de manutenção',
            'Compra de insumos',
            'Vistoria',
            'Apresentação comercial',
            null, // Alguns sem propósito
        ];

        // Inicializar odômetros para cada veículo (usando current_odometer atual)
        $vehicleOdometers = [];
        foreach ($vehicles as $vehicle) {
            $vehicleOdometers[$vehicle->id] = $vehicle->current_odometer ?? $vehicle->km_inicial ?? 0;
        }

        // Data inicial (6 meses atrás)
        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now();

        $tripsCreated = 0;
        $progress = 0;

        // Criar 500 percursos
        for ($i = 0; $i < 500; $i++) {
            // Selecionar veículo aleatório
            $vehicle = $vehicles->random();
            
            // Selecionar motorista que tem relação com o veículo
            $vehicleDrivers = $drivers->filter(function($driver) use ($vehicle) {
                return $driver->vehicles->contains($vehicle->id);
            });

            if ($vehicleDrivers->isEmpty()) {
                // Se nenhum motorista tem relação, usar qualquer motorista
                $driver = $drivers->random();
            } else {
                $driver = $vehicleDrivers->random();
            }

            // Obter odômetro atual do veículo
            $currentOdometer = $vehicleOdometers[$vehicle->id];

            // Gerar data aleatória entre startDate e endDate
            $randomDays = rand(0, $startDate->diffInDays($endDate));
            $date = $startDate->copy()->addDays($randomDays);

            // Selecionar locais de origem e destino (diferentes)
            do {
                $origin = $locations->random();
                $destination = $locations->random();
            } while ($origin->id === $destination->id);

            // Gerar horários
            $departureHour = rand(6, 18); // Entre 6h e 18h
            $departureMinute = [0, 15, 30, 45][rand(0, 3)];
            $departureTime = Carbon::createFromTime($departureHour, $departureMinute, 0);

            // Calcular KM do percurso (entre 10 e 300 km)
            $tripKm = rand(10, 300);

            // Odômetro inicial (pode ser um pouco maior que o atual se já houve outros percursos no mesmo dia)
            $odometerStart = $currentOdometer;

            // Odômetro final
            $odometerEnd = $odometerStart + $tripKm;

            // Tempo de retorno (se aplicável - 60% dos percursos retornam)
            $returnToOrigin = rand(1, 100) <= 60;
            $returnTime = null;

            if ($returnToOrigin) {
                // Tempo de viagem: 1 a 4 horas
                $travelHours = rand(1, 4);
                $returnTime = $departureTime->copy()->addHours($travelHours);
                // Se voltar, dobrar o KM
                $tripKm = $tripKm * 2;
                $odometerEnd = $odometerStart + $tripKm;
            } else {
                // Viagem só de ida, tempo estimado
                $travelHours = rand(1, 3);
                $returnTime = $departureTime->copy()->addHours($travelHours);
            }

            // Criar percurso
            DB::transaction(function () use (
                $vehicle,
                $driver,
                $date,
                $origin,
                $destination,
                $returnToOrigin,
                $departureTime,
                $returnTime,
                $odometerStart,
                $odometerEnd,
                $purposes,
                $admin,
                &$vehicleOdometers,
                &$tripsCreated
            ) {
                $trip = Trip::create([
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $driver->id,
                    'date' => $date->format('Y-m-d'),
                    'origin_location_id' => $origin->id,
                    'destination_location_id' => $destination->id,
                    'return_to_origin' => $returnToOrigin,
                    'departure_time' => $departureTime->format('H:i:s'),
                    'return_time' => $returnTime ? $returnTime->format('H:i:s') : null,
                    'odometer_start' => $odometerStart,
                    'odometer_end' => $odometerEnd,
                    'km_total' => $odometerEnd - $odometerStart,
                    'purpose' => $purposes[array_rand($purposes)],
                    'created_by' => $admin ? $admin->id : $driver->id,
                ]);

                // Atualizar odômetro do veículo
                $vehicleOdometers[$vehicle->id] = $odometerEnd;
                
                // Atualizar no banco também
                $vehicle->current_odometer = $odometerEnd;
                $vehicle->save();

                $tripsCreated++;
            });

            // Mostrar progresso a cada 50 percursos
            if (($i + 1) % 50 == 0) {
                $progress = $i + 1;
                $this->command->info("Progresso: {$progress}/500 percursos criados...");
            }
        }

        // Atualizar odômetros finais de todos os veículos
        foreach ($vehicleOdometers as $vehicleId => $finalOdometer) {
            Vehicle::where('id', $vehicleId)->update(['current_odometer' => $finalOdometer]);
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  500 PERCURSOS CRIADOS COM SUCESSO!');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info("Total de percursos criados: {$tripsCreated}");
        $this->command->info('');
        $this->command->info('Odômetros atualizados dos veículos:');
        foreach ($vehicles as $vehicle) {
            $vehicle->refresh();
            $this->command->info("  - {$vehicle->name} ({$vehicle->plate}): {$vehicle->current_odometer} km");
        }
        $this->command->info('');
    }
}
