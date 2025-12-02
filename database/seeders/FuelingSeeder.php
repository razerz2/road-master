<?php

namespace Database\Seeders;

use App\Models\Fueling;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\Location;
use App\Models\PaymentMethod;
use App\Models\FuelType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FuelingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicles = Vehicle::all();
        $drivers = User::where('role', 'motorista')->get();
        $locations = Location::where('type', 'posto_combustivel')->get();
        $paymentMethods = PaymentMethod::where('active', true)->get();
        $fuelTypes = FuelType::where('active', true)->get();
        $admin = User::where('role', 'admin')->first();

        if ($vehicles->isEmpty() || $drivers->isEmpty()) {
            $this->command->error('É necessário ter veículos e motoristas cadastrados!');
            return;
        }

        // Consumo médio por tipo de veículo (km por litro)
        $consumptionRates = [
            // Veículos pequenos: 10-14 km/l
            'Gol' => rand(11, 13),
            'Onix' => rand(12, 14),
            'Uno' => rand(10, 12),
            'HB20' => rand(11, 13),
            
            // Veículos médios: 9-12 km/l
            'Corolla' => rand(10, 12),
            'Civic' => rand(9, 11),
            
            // SUVs: 8-11 km/l
            'Duster' => rand(9, 11),
            
            // Pickups: 7-10 km/l
            'Ranger' => rand(8, 10),
        ];

        // Preços médios de combustível (por litro)
        $fuelPrices = [
            'Gasolina' => rand(520, 580) / 100, // R$ 5,20 a R$ 5,80
            'Etanol' => rand(380, 420) / 100,   // R$ 3,80 a R$ 4,20
            'Diesel' => rand(550, 600) / 100,   // R$ 5,50 a R$ 6,00
            'Flex' => rand(520, 580) / 100,     // R$ 5,20 a R$ 5,80 (gasolina)
            'GNV' => rand(450, 500) / 100,      // R$ 4,50 a R$ 5,00
        ];

        // Nomes de postos
        $gasStationNames = [
            'Posto Shell', 'Posto Ipiranga', 'Posto BR', 'Posto Petrobras',
            'Auto Posto', 'Posto 24h', 'Posto Express', 'Posto Total',
        ];

        // Criar abastecimentos para cada veículo
        $totalFuelings = 0;

        foreach ($vehicles as $vehicle) {
            // Buscar percursos do veículo ordenados por data
            $trips = Trip::where('vehicle_id', $vehicle->id)
                ->orderBy('date')
                ->orderBy('departure_time')
                ->get();

            if ($trips->isEmpty()) {
                continue;
            }

            // Obter tipo de combustível do veículo
            $vehicleFuelTypes = $vehicle->fuelTypes;
            if ($vehicleFuelTypes->isEmpty()) {
                // Se não tiver tipo, usar flex como padrão
                $defaultFuelType = $fuelTypes->where('slug', 'flex')->first();
                if (!$defaultFuelType) {
                    $defaultFuelType = $fuelTypes->first();
                }
                $vehicleFuelType = $defaultFuelType;
                $fuelTypeName = $vehicleFuelType ? $vehicleFuelType->name : 'Gasolina';
            } else {
                $vehicleFuelType = $vehicleFuelTypes->first();
                $fuelTypeName = $vehicleFuelType->name;
            }

            // Determinar consumo do veículo
            $consumptionKmPerLiter = 10; // padrão
            foreach ($consumptionRates as $model => $rate) {
                if (stripos($vehicle->model ?? '', $model) !== false || 
                    stripos($vehicle->name ?? '', $model) !== false) {
                    $consumptionKmPerLiter = $rate;
                    break;
                }
            }

            // Capacidade do tanque
            $tankCapacity = $vehicle->tank_capacity ?? 50;

            // Iniciar com tanque cheio (ou parcialmente cheio)
            $currentFuelLevel = $tankCapacity * (rand(60, 90) / 100); // 60-90% do tanque
            $currentOdometer = $vehicle->km_inicial ?? 0;

            // Percorrer os percursos e criar abastecimentos
            foreach ($trips as $trip) {
                // Verificar se precisa abastecer antes da viagem
                $tripKm = $trip->km_total;
                $fuelNeeded = $tripKm / $consumptionKmPerLiter;
                
                // Se o combustível atual não for suficiente (deixar margem de 20%)
                if ($currentFuelLevel < ($fuelNeeded * 1.2)) {
                    // Abastecer antes da viagem
                    $refuelLiters = min($tankCapacity, $tankCapacity * (rand(70, 95) / 100));
                    $refuelLiters = round($refuelLiters, 2);
                    
                    // Usar odômetro de início da viagem (ou um pouco antes)
                    $fuelingOdometer = max($currentOdometer, $trip->odometer_start - rand(0, 50));
                    
                    // Data/hora do abastecimento (algumas horas antes da viagem)
                    $fuelingDateTime = Carbon::parse($trip->date)
                        ->setTimeFromTimeString($trip->departure_time)
                        ->subHours(rand(1, 4))
                        ->subMinutes(rand(0, 59));

                    // Preço do combustível
                    $pricePerLiter = $fuelPrices[$fuelTypeName] ?? $fuelPrices['Gasolina'];
                    $totalAmount = round($refuelLiters * $pricePerLiter, 2);

                    // Selecionar posto e método de pagamento
                    $gasStation = $locations->isNotEmpty() ? $locations->random() : null;
                    $gasStationName = $gasStation 
                        ? $gasStation->name 
                        : $gasStationNames[array_rand($gasStationNames)];
                    
                    $paymentMethod = $paymentMethods->isNotEmpty() 
                        ? $paymentMethods->random() 
                        : null;

                    // Motorista responsável (pode ser o motorista da viagem ou outro)
                    $driver = $drivers->filter(function($d) use ($vehicle) {
                        return $d->vehicles->contains($vehicle->id);
                    })->random() ?? $drivers->random();

                    DB::transaction(function () use (
                        $vehicle,
                        $driver,
                        $fuelingDateTime,
                        $fuelingOdometer,
                        $fuelTypeName,
                        $refuelLiters,
                        $pricePerLiter,
                        $totalAmount,
                        $gasStationName,
                        $paymentMethod,
                        $admin,
                        &$currentFuelLevel,
                        &$totalFuelings
                    ) {
                        Fueling::create([
                            'vehicle_id' => $vehicle->id,
                            'user_id' => $admin ? $admin->id : $driver->id,
                            'date_time' => $fuelingDateTime,
                            'odometer' => $fuelingOdometer,
                            'fuel_type' => $fuelTypeName,
                            'liters' => $refuelLiters,
                            'price_per_liter' => $pricePerLiter,
                            'total_amount' => $totalAmount,
                            'gas_station_name' => $gasStationName,
                            'payment_method' => $paymentMethod ? $paymentMethod->name : null,
                            'payment_method_id' => $paymentMethod ? $paymentMethod->id : null,
                            'notes' => rand(1, 10) <= 2 ? 'Abastecimento antes de viagem' : null,
                        ]);

                        $currentFuelLevel = $refuelLiters;
                        $totalFuelings++;
                    });
                }

                // Atualizar nível de combustível após a viagem
                $currentFuelLevel -= $fuelNeeded;
                $currentOdometer = $trip->odometer_end;

                // Se o tanque estiver muito baixo após a viagem, abastecer
                if ($currentFuelLevel < ($tankCapacity * 0.3)) {
                    $refuelLiters = min($tankCapacity, $tankCapacity * (rand(70, 95) / 100));
                    $refuelLiters = round($refuelLiters, 2);
                    
                    // Data/hora após a viagem
                    $returnTime = $trip->return_time 
                        ? Carbon::parse($trip->date)->setTimeFromTimeString($trip->return_time)
                        : Carbon::parse($trip->date)->setTimeFromTimeString($trip->departure_time)->addHours(2);
                    
                    $fuelingDateTime = $returnTime->addHours(rand(1, 3))->addMinutes(rand(0, 59));
                    $fuelingOdometer = $trip->odometer_end;

                    // Preço (pode variar um pouco)
                    $pricePerLiter = ($fuelPrices[$fuelTypeName] ?? $fuelPrices['Gasolina']) + (rand(-20, 20) / 100);
                    $pricePerLiter = max(3.00, $pricePerLiter); // Mínimo R$ 3,00
                    $totalAmount = round($refuelLiters * $pricePerLiter, 2);

                    // Selecionar posto e método
                    $gasStation = $locations->isNotEmpty() ? $locations->random() : null;
                    $gasStationName = $gasStation 
                        ? $gasStation->name 
                        : $gasStationNames[array_rand($gasStationNames)];
                    
                    $paymentMethod = $paymentMethods->isNotEmpty() 
                        ? $paymentMethods->random() 
                        : null;

                    $driver = $drivers->filter(function($d) use ($vehicle) {
                        return $d->vehicles->contains($vehicle->id);
                    })->random() ?? $drivers->random();

                    DB::transaction(function () use (
                        $vehicle,
                        $driver,
                        $fuelingDateTime,
                        $fuelingOdometer,
                        $fuelTypeName,
                        $refuelLiters,
                        $pricePerLiter,
                        $totalAmount,
                        $gasStationName,
                        $paymentMethod,
                        $admin,
                        &$currentFuelLevel,
                        &$totalFuelings
                    ) {
                        Fueling::create([
                            'vehicle_id' => $vehicle->id,
                            'user_id' => $admin ? $admin->id : $driver->id,
                            'date_time' => $fuelingDateTime,
                            'odometer' => $fuelingOdometer,
                            'fuel_type' => $fuelTypeName,
                            'liters' => $refuelLiters,
                            'price_per_liter' => $pricePerLiter,
                            'total_amount' => $totalAmount,
                            'gas_station_name' => $gasStationName,
                            'payment_method' => $paymentMethod ? $paymentMethod->name : null,
                            'payment_method_id' => $paymentMethod ? $paymentMethod->id : null,
                            'notes' => rand(1, 10) <= 2 ? 'Abastecimento após viagem' : null,
                        ]);

                        $currentFuelLevel = $refuelLiters;
                        $totalFuelings++;
                    });
                }
            }

            // Criar alguns abastecimentos adicionais regulares (mesmo sem viagens)
            // Abastecimentos periódicos a cada ~500-800 km
            $additionalFuelings = rand(3, 7);
            $fuelingOdometer = $vehicle->km_inicial ?? 0;
            
            for ($i = 0; $i < $additionalFuelings; $i++) {
                $fuelingOdometer += rand(500, 800);
                
                // Não criar se passar do último odômetro
                if ($fuelingOdometer > $vehicle->current_odometer) {
                    break;
                }

                // Data aleatória no período
                $fuelingDateTime = Carbon::now()->subMonths(rand(1, 6))
                    ->subDays(rand(0, 30))
                    ->setTime(rand(6, 22), rand(0, 59), 0);

                $refuelLiters = min($tankCapacity, $tankCapacity * (rand(70, 95) / 100));
                $refuelLiters = round($refuelLiters, 2);
                
                $pricePerLiter = $fuelPrices[$fuelTypeName] ?? $fuelPrices['Gasolina'];
                $totalAmount = round($refuelLiters * $pricePerLiter, 2);

                $gasStation = $locations->isNotEmpty() ? $locations->random() : null;
                $gasStationName = $gasStation 
                    ? $gasStation->name 
                    : $gasStationNames[array_rand($gasStationNames)];
                
                $paymentMethod = $paymentMethods->isNotEmpty() 
                    ? $paymentMethods->random() 
                    : null;

                $driver = $drivers->filter(function($d) use ($vehicle) {
                    return $d->vehicles->contains($vehicle->id);
                })->random() ?? $drivers->random();

                DB::transaction(function () use (
                    $vehicle,
                    $driver,
                    $fuelingDateTime,
                    $fuelingOdometer,
                    $fuelTypeName,
                    $refuelLiters,
                    $pricePerLiter,
                    $totalAmount,
                    $gasStationName,
                    $paymentMethod,
                    $admin,
                    &$totalFuelings
                ) {
                    Fueling::create([
                        'vehicle_id' => $vehicle->id,
                        'user_id' => $admin ? $admin->id : $driver->id,
                        'date_time' => $fuelingDateTime,
                        'odometer' => $fuelingOdometer,
                        'fuel_type' => $fuelTypeName,
                        'liters' => $refuelLiters,
                        'price_per_liter' => $pricePerLiter,
                        'total_amount' => $totalAmount,
                        'gas_station_name' => $gasStationName,
                        'payment_method' => $paymentMethod ? $paymentMethod->name : null,
                        'payment_method_id' => $paymentMethod ? $paymentMethod->id : null,
                    ]);

                    $totalFuelings++;
                });
            }
        }

        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('  ABASTECIMENTOS CRIADOS COM SUCESSO!');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info("Total de abastecimentos criados: {$totalFuelings}");
        $this->command->info('');
        
        // Estatísticas por veículo
        foreach ($vehicles as $vehicle) {
            $vehicleFuelings = Fueling::where('vehicle_id', $vehicle->id)->get();
            $totalLiters = $vehicleFuelings->sum('liters');
            $totalCost = $vehicleFuelings->sum('total_amount');
            
            $vehicleTrips = Trip::where('vehicle_id', $vehicle->id)->get();
            $totalKm = $vehicleTrips->sum('km_total');
            $avgConsumption = $totalLiters > 0 ? round($totalKm / $totalLiters, 2) : 0;
            
            $this->command->info("{$vehicle->name} ({$vehicle->plate}):");
            $this->command->info("  - Abastecimentos: {$vehicleFuelings->count()}");
            $this->command->info("  - Total de litros: " . number_format($totalLiters, 2, ',', '.') . " L");
            $this->command->info("  - Total gasto: R$ " . number_format($totalCost, 2, ',', '.'));
            $this->command->info("  - KM rodados: " . number_format($totalKm, 0, ',', '.') . " km");
            $this->command->info("  - Média de consumo: {$avgConsumption} km/L");
            $this->command->info('');
        }
    }
}
