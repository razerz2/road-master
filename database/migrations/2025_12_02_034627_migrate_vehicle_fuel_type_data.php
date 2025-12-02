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
        // Migrar dados existentes de fuel_type (string) para a nova estrutura
        $vehicles = \DB::table('vehicles')->whereNotNull('fuel_type')->get();
        
        foreach ($vehicles as $vehicle) {
            if (!empty($vehicle->fuel_type)) {
                $fuelTypes = array_map('trim', explode(',', $vehicle->fuel_type));
                
                foreach ($fuelTypes as $fuelTypeName) {
                    if (empty($fuelTypeName)) {
                        continue;
                    }
                    
                    // Criar ou buscar o tipo de combustível
                    $slug = \Illuminate\Support\Str::slug($fuelTypeName);
                    $fuelType = \DB::table('fuel_types')
                        ->where('slug', $slug)
                        ->first();
                    
                    if (!$fuelType) {
                        $fuelTypeId = \DB::table('fuel_types')->insertGetId([
                            'name' => ucfirst($fuelTypeName),
                            'slug' => $slug,
                            'active' => true,
                            'order' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $fuelTypeId = $fuelType->id;
                    }
                    
                    // Criar relação se não existir
                    $exists = \DB::table('vehicle_fuel_type')
                        ->where('vehicle_id', $vehicle->id)
                        ->where('fuel_type_id', $fuelTypeId)
                        ->exists();
                    
                    if (!$exists) {
                        \DB::table('vehicle_fuel_type')->insert([
                            'vehicle_id' => $vehicle->id,
                            'fuel_type_id' => $fuelTypeId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
        
        // Criar tipos padrão se não existirem
        $defaultFuelTypes = ['gasolina', 'etanol', 'diesel', 'flex', 'gnv'];
        foreach ($defaultFuelTypes as $fuelTypeName) {
            $slug = \Illuminate\Support\Str::slug($fuelTypeName);
            $exists = \DB::table('fuel_types')->where('slug', $slug)->exists();
            
            if (!$exists) {
                \DB::table('fuel_types')->insert([
                    'name' => ucfirst($fuelTypeName),
                    'slug' => $slug,
                    'active' => true,
                    'order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter migração: converter relações de volta para string separada por vírgula
        $vehicles = \DB::table('vehicles')->get();
        
        foreach ($vehicles as $vehicle) {
            $fuelTypes = \DB::table('vehicle_fuel_type')
                ->join('fuel_types', 'vehicle_fuel_type.fuel_type_id', '=', 'fuel_types.id')
                ->where('vehicle_fuel_type.vehicle_id', $vehicle->id)
                ->pluck('fuel_types.name')
                ->toArray();
            
            $fuelTypeString = !empty($fuelTypes) ? implode(', ', $fuelTypes) : null;
            
            \DB::table('vehicles')
                ->where('id', $vehicle->id)
                ->update(['fuel_type' => $fuelTypeString]);
        }
    }
};
