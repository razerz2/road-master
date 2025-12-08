<?php

namespace Database\Seeders;

use App\Models\GasStation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GasStationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar posto genérico que pode representar qualquer posto
        GasStation::firstOrCreate(
            ['slug' => 'posto-generico'],
            [
                'name' => 'Posto Genérico',
                'description' => 'Posto genérico que pode representar qualquer posto de combustível',
                'active' => true,
                'order' => 0,
            ]
        );
    }
}
