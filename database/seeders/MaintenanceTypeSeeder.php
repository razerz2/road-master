<?php

namespace Database\Seeders;

use App\Models\MaintenanceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MaintenanceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultMaintenanceTypes = ['troca_oleo', 'revisao', 'pneu', 'freio', 'suspensao', 'outro'];
        
        $descriptions = [
            'troca_oleo' => 'Troca de óleo do motor',
            'revisao' => 'Revisão geral do veículo',
            'pneu' => 'Serviços relacionados a pneus',
            'freio' => 'Serviços relacionados ao sistema de freios',
            'suspensao' => 'Serviços relacionados à suspensão',
            'outro' => 'Outros tipos de manutenção',
        ];
        
        foreach ($defaultMaintenanceTypes as $index => $typeName) {
            $slug = Str::slug($typeName);
            $name = ucfirst(str_replace('_', ' ', $typeName));
            
            MaintenanceType::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $descriptions[$typeName] ?? null,
                    'active' => true,
                    'order' => $index,
                ]
            );
        }

        $this->command->info('Tipos de manutenção criados com sucesso!');
    }
}
