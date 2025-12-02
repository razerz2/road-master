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
        $defaultPaymentMethods = ['dinheiro', 'cartao_debito', 'cartao_credito', 'pix', 'cheque'];
        foreach ($defaultPaymentMethods as $index => $methodName) {
            $slug = \Illuminate\Support\Str::slug($methodName);
            $exists = \DB::table('payment_methods')->where('slug', $slug)->exists();
            
            if (!$exists) {
                \DB::table('payment_methods')->insert([
                    'name' => ucfirst(str_replace('_', ' ', $methodName)),
                    'slug' => $slug,
                    'active' => true,
                    'order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Migrar dados existentes
        $fuelings = \DB::table('fuelings')->whereNotNull('payment_method')->get();
        
        foreach ($fuelings as $fueling) {
            if (!empty($fueling->payment_method)) {
                $slug = \Illuminate\Support\Str::slug($fueling->payment_method);
                $paymentMethod = \DB::table('payment_methods')->where('slug', $slug)->first();
                
                if (!$paymentMethod) {
                    $paymentMethodId = \DB::table('payment_methods')->insertGetId([
                        'name' => ucfirst($fueling->payment_method),
                        'slug' => $slug,
                        'active' => true,
                        'order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $paymentMethodId = $paymentMethod->id;
                }
                
                \DB::table('fuelings')
                    ->where('id', $fueling->id)
                    ->update(['payment_method_id' => $paymentMethodId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter: converter payment_method_id de volta para payment_method (string)
        $fuelings = \DB::table('fuelings')
            ->join('payment_methods', 'fuelings.payment_method_id', '=', 'payment_methods.id')
            ->select('fuelings.id', 'payment_methods.name')
            ->get();
        
        foreach ($fuelings as $fueling) {
            \DB::table('fuelings')
                ->where('id', $fueling->id)
                ->update(['payment_method' => $fueling->name]);
        }
    }
};
