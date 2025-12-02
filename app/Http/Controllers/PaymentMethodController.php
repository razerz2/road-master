<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class); // Apenas admin pode acessar
        
        $paymentMethods = PaymentMethod::orderBy('order')->orderBy('name')->get();
        
        $paymentMethod = null;
        $editingId = null;
        
        if ($request->has('edit')) {
            $paymentMethod = PaymentMethod::findOrFail($request->edit);
            $editingId = $paymentMethod->id;
        }
        
        return view('payment-methods.index', compact('paymentMethods', 'paymentMethod', 'editingId'));
    }

    public function store(Request $request)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_methods,name',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = $request->has('active') ? true : false;
        $validated['order'] = $validated['order'] ?? 0;

        PaymentMethod::create($validated);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Método de pagamento criado com sucesso!');
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_methods,name,' . $paymentMethod->id,
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['active'] = $request->has('active') ? true : false;
        $validated['order'] = $validated['order'] ?? 0;

        $paymentMethod->update($validated);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Método de pagamento atualizado com sucesso!');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        Gate::authorize('viewAny', \App\Models\User::class);

        // Verificar se há abastecimentos associados
        if ($paymentMethod->fuelings()->count() > 0) {
            return redirect()->route('payment-methods.index')
                ->with('error', 'Não é possível excluir o método de pagamento pois existem abastecimentos associados a ele.');
        }

        $paymentMethod->delete();

        return redirect()->route('payment-methods.index')
            ->with('success', 'Método de pagamento excluído com sucesso!');
    }
}
