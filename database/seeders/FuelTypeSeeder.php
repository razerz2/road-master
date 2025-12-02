<?php

namespace Database\Seeders;

use App\Models\FuelType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FuelTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultFuelTypes = ['gasolina', 'etanol', 'diesel', 'flex', 'gnv'];
        
        $descriptions = [
            'gasolina' => 'Gasolina comum',
            'etanol' => 'Álcool etílico',
            'diesel' => 'Diesel comum',
            'flex' => 'Flexfuel (gasolina/etanol)',
            'gnv' => 'Gás Natural Veicular',
        ];
        
        foreach ($defaultFuelTypes as $index => $fuelTypeName) {
            $slug = Str::slug($fuelTypeName);
            $name = ucfirst($fuelTypeName);
            
            FuelType::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $descriptions[$fuelTypeName] ?? null,
                    'active' => true,
                    'order' => $index,
                ]
            );
        }

        $this->command->info('Tipos de combustível criados com sucesso!');
    }
}
