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
        // Criar tipos padrão
        $defaultMaintenanceTypes = ['troca_oleo', 'revisao', 'pneu', 'freio', 'suspensao', 'outro'];
        foreach ($defaultMaintenanceTypes as $index => $typeName) {
            $slug = \Illuminate\Support\Str::slug($typeName);
            $exists = \DB::table('maintenance_types')->where('slug', $slug)->exists();
            
            if (!$exists) {
                \DB::table('maintenance_types')->insert([
                    'name' => ucfirst(str_replace('_', ' ', $typeName)),
                    'slug' => $slug,
                    'active' => true,
                    'order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Migrar dados existentes
        $maintenances = \DB::table('maintenances')->get();
        
        foreach ($maintenances as $maintenance) {
            if (!empty($maintenance->type)) {
                $slug = \Illuminate\Support\Str::slug($maintenance->type);
                $maintenanceType = \DB::table('maintenance_types')->where('slug', $slug)->first();
                
                if ($maintenanceType) {
                    \DB::table('maintenances')
                        ->where('id', $maintenance->id)
                        ->update(['maintenance_type_id' => $maintenanceType->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter: os dados originais estão no campo 'type' (enum), não precisa reverter
    }
};
