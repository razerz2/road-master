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
        $defaultLocationTypes = ['empresa', 'cliente', 'posto_combustivel', 'outro'];
        foreach ($defaultLocationTypes as $index => $typeName) {
            $slug = \Illuminate\Support\Str::slug($typeName);
            $exists = \DB::table('location_types')->where('slug', $slug)->exists();
            
            if (!$exists) {
                \DB::table('location_types')->insert([
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
        $locations = \DB::table('locations')->get();
        
        foreach ($locations as $location) {
            if (!empty($location->type)) {
                $slug = \Illuminate\Support\Str::slug($location->type);
                $locationType = \DB::table('location_types')->where('slug', $slug)->first();
                
                if ($locationType) {
                    \DB::table('locations')
                        ->where('id', $location->id)
                        ->update(['location_type_id' => $locationType->id]);
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
