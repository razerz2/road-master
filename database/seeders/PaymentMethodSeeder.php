<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPaymentMethods = ['dinheiro', 'cartao_debito', 'cartao_credito', 'pix', 'cheque'];
        
        foreach ($defaultPaymentMethods as $index => $methodName) {
            $slug = Str::slug($methodName);
            $name = ucfirst(str_replace('_', ' ', $methodName));
            
            PaymentMethod::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => 'Pagamento via ' . strtolower($name),
                    'active' => true,
                    'order' => $index,
                ]
            );
        }

        $this->command->info('Métodos de pagamento criados com sucesso!');
    }
}
