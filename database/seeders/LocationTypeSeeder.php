<?php

namespace Database\Seeders;

use App\Models\LocationType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultLocationTypes = ['empresa', 'cliente', 'posto_combustivel', 'outro'];
        
        $descriptions = [
            'empresa' => 'Local da empresa',
            'cliente' => 'Local de cliente',
            'posto_combustivel' => 'Posto de abastecimento',
            'outro' => 'Outros tipos de local',
        ];
        
        foreach ($defaultLocationTypes as $index => $typeName) {
            $slug = Str::slug($typeName);
            $name = ucfirst(str_replace('_', ' ', $typeName));
            
            LocationType::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $descriptions[$typeName] ?? null,
                    'active' => true,
                    'order' => $index,
                ]
            );
        }

        $this->command->info('Tipos de local criados com sucesso!');
    }
}
