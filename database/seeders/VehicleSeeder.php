<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\FuelType;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar tipos de combustível por slug
        $gasolina = FuelType::where('slug', 'gasolina')->first();
        $etanol = FuelType::where('slug', 'etanol')->first();
        $diesel = FuelType::where('slug', 'diesel')->first();
        $flex = FuelType::where('slug', 'flex')->first();
        $gnv = FuelType::where('slug', 'gnv')->first();

        $vehicles = [
            [
                'name' => 'VW Gol 1.0',
                'plate' => 'ABC-1234',
                'brand' => 'Volkswagen',
                'model' => 'Gol',
                'year' => 2020,
                'fuel_type' => 'flex', // campo legado
                'tank_capacity' => 55.00,
                'km_inicial' => 0,
                'current_odometer' => 15230,
                'active' => true,
                'fuel_type_ids' => $flex ? [$flex->id] : [],
            ],
            [
                'name' => 'Toyota Corolla',
                'plate' => 'XYZ-5678',
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'year' => 2022,
                'fuel_type' => 'flex',
                'tank_capacity' => 50.00,
                'km_inicial' => 0,
                'current_odometer' => 8730,
                'active' => true,
                'fuel_type_ids' => $flex ? [$flex->id] : [],
            ],
            [
                'name' => 'Ford Ranger',
                'plate' => 'DEF-9012',
                'brand' => 'Ford',
                'model' => 'Ranger',
                'year' => 2021,
                'fuel_type' => 'diesel',
                'tank_capacity' => 80.00,
                'km_inicial' => 0,
                'current_odometer' => 45890,
                'active' => true,
                'fuel_type_ids' => $diesel ? [$diesel->id] : [],
            ],
            [
                'name' => 'Chevrolet Onix',
                'plate' => 'GHI-3456',
                'brand' => 'Chevrolet',
                'model' => 'Onix',
                'year' => 2023,
                'fuel_type' => 'flex',
                'tank_capacity' => 54.00,
                'km_inicial' => 0,
                'current_odometer' => 5420,
                'active' => true,
                'fuel_type_ids' => $flex ? [$flex->id] : [],
            ],
            [
                'name' => 'Fiat Uno',
                'plate' => 'JKL-7890',
                'brand' => 'Fiat',
                'model' => 'Uno',
                'year' => 2019,
                'fuel_type' => 'flex',
                'tank_capacity' => 47.00,
                'km_inicial' => 0,
                'current_odometer' => 67890,
                'active' => true,
                'fuel_type_ids' => $flex ? [$flex->id] : [],
            ],
            [
                'name' => 'Honda Civic',
                'plate' => 'MNO-2468',
                'brand' => 'Honda',
                'model' => 'Civic',
                'year' => 2022,
                'fuel_type' => 'flex',
                'tank_capacity' => 47.00,
                'km_inicial' => 0,
                'current_odometer' => 32450,
                'active' => true,
                'fuel_type_ids' => $flex ? [$flex->id] : [],
            ],
            [
                'name' => 'Renault Duster',
                'plate' => 'PQR-1357',
                'brand' => 'Renault',
                'model' => 'Duster',
                'year' => 2021,
                'fuel_type' => 'flex',
                'tank_capacity' => 60.00,
                'km_inicial' => 0,
                'current_odometer' => 56780,
                'active' => true,
                'fuel_type_ids' => $flex ? [$flex->id] : [],
            ],
            [
                'name' => 'Hyundai HB20',
                'plate' => 'STU-8024',
                'brand' => 'Hyundai',
                'model' => 'HB20',
                'year' => 2023,
                'fuel_type' => 'flex',
                'tank_capacity' => 50.00,
                'km_inicial' => 0,
                'current_odometer' => 12340,
                'active' => true,
                'fuel_type_ids' => $flex ? [$flex->id] : [],
            ],
        ];

        foreach ($vehicles as $vehicleData) {
            $fuelTypeIds = $vehicleData['fuel_type_ids'];
            unset($vehicleData['fuel_type_ids']);

            $vehicle = Vehicle::create($vehicleData);
            
            if (!empty($fuelTypeIds)) {
                $vehicle->fuelTypes()->sync($fuelTypeIds);
            }

            $this->command->info("Veículo cadastrado: {$vehicle->name} - {$vehicle->plate}");
        }

        $this->command->info('8 veículos cadastrados com sucesso!');
    }
}
