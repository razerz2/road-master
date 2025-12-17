<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Novo Abastecimento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('fuelings.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="vehicle_id" :value="__('Veículo')" />
                                <select id="vehicle_id" name="vehicle_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Selecione...</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->name }} - {{ $vehicle->plate }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                            </div>

                            @if(auth()->user()->role === 'admin')
                            <div>
                                <x-input-label for="user_id" :value="__('Usuário')" />
                                <div class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <select id="user_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm bg-gray-100 dark:bg-gray-700" required disabled>
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->id }}" {{ old('user_id', auth()->id()) == $driver->id ? 'selected' : '' }}>{{ $driver->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" id="user_id_hidden" name="user_id" value="{{ old('user_id', auth()->id()) }}">
                                        <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                                    </div>
                                    <button type="button" id="toggle_user_select" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm whitespace-nowrap mb-1" title="Habilitar seleção de usuário">
                                        <span id="toggle_user_text">Habilitar</span>
                                    </button>
                                </div>
                            </div>
                            @elseif(auth()->user()->role === 'condutor')
                            {{-- Condutor sempre usa seu próprio ID, campo oculto --}}
                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                            @else
                            {{-- Outros usuários também usam campo oculto --}}
                            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                            @endif

                            <div>
                                <x-input-label for="date_time" :value="__('Data e Hora')" />
                                <x-text-input id="date_time" class="block mt-1 w-full" type="datetime-local" name="date_time" :value="old('date_time', now()->format('Y-m-d\TH:i'))" required />
                                <x-input-error :messages="$errors->get('date_time')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="odometer" :value="__('KM no Momento')" />
                                <x-text-input id="odometer" class="block mt-1 w-full" type="number" name="odometer" :value="old('odometer')" required />
                                <x-input-error :messages="$errors->get('odometer')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="fuel_type" :value="__('Tipo de Combustível')" />
                                <select id="fuel_type" name="fuel_type" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                                    <option value="">Selecione...</option>
                                    @foreach($fuelTypes as $fuelType)
                                        <option value="{{ $fuelType->name }}" {{ old('fuel_type') == $fuelType->name ? 'selected' : '' }}>
                                            {{ $fuelType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('fuel_type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="liters" :value="__('Litros')" />
                                <x-text-input id="liters" class="block mt-1 w-full" type="number" step="0.01" name="liters" :value="old('liters')" required />
                                <x-input-error :messages="$errors->get('liters')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="price_per_liter" :value="__('Preço por Litro')" />
                                <x-text-input id="price_per_liter" class="block mt-1 w-full" type="number" step="0.01" name="price_per_liter" :value="old('price_per_liter')" required />
                                <x-input-error :messages="$errors->get('price_per_liter')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="total_amount" :value="__('Valor Total (deixe vazio para calcular)')" />
                                <x-text-input id="total_amount" class="block mt-1 w-full" type="number" step="0.01" name="total_amount" :value="old('total_amount')" />
                                <x-input-error :messages="$errors->get('total_amount')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="gas_station_id" :value="__('Posto')" />
                                <select id="gas_station_id" name="gas_station_id" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                    <option value="">Selecione um posto</option>
                                    @foreach($gasStations as $gasStation)
                                        <option value="{{ $gasStation->id }}" {{ old('gas_station_id') == $gasStation->id ? 'selected' : '' }}>
                                            {{ $gasStation->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('gas_station_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="payment_method_id" :value="__('Forma de Pagamento')" />
                                <select id="payment_method_id" name="payment_method_id" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                    <option value="">Selecione...</option>
                                    @foreach($paymentMethods as $paymentMethod)
                                        <option value="{{ $paymentMethod->id }}" {{ old('payment_method_id') == $paymentMethod->id ? 'selected' : '' }}>
                                            {{ $paymentMethod->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('payment_method_id')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Observações')" />
                                <textarea id="notes" name="notes" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('fuelings.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <x-primary-button>
                                {{ __('Salvar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Controlar habilitação/desabilitação do campo de usuário para admin
        @if(auth()->user()->role === 'admin')
        document.addEventListener('DOMContentLoaded', function() {
            const userSelect = document.getElementById('user_id');
            const userHidden = document.getElementById('user_id_hidden');
            const toggleButton = document.getElementById('toggle_user_select');
            const toggleText = document.getElementById('toggle_user_text');
            let userSelectEnabled = false;
            const adminUserId = {{ auth()->id() }};
            
            if (userSelect && toggleButton && userHidden) {
                // Sincronizar valor do select com o hidden quando mudar
                userSelect.addEventListener('change', function() {
                    userHidden.value = this.value;
                });
                
                toggleButton.addEventListener('click', function() {
                    userSelectEnabled = !userSelectEnabled;
                    
                    if (userSelectEnabled) {
                        // Habilitar campo
                        userSelect.disabled = false;
                        userSelect.classList.remove('bg-gray-100', 'dark:bg-gray-700');
                        userSelect.classList.add('bg-white', 'dark:bg-gray-900');
                        toggleText.textContent = 'Desabilitar';
                        toggleButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        toggleButton.classList.add('bg-gray-600', 'hover:bg-gray-700');
                    } else {
                        // Desabilitar campo e voltar para admin logado
                        userSelect.disabled = true;
                        userSelect.value = adminUserId;
                        userHidden.value = adminUserId;
                        userSelect.classList.remove('bg-white', 'dark:bg-gray-900');
                        userSelect.classList.add('bg-gray-100', 'dark:bg-gray-700');
                        toggleText.textContent = 'Habilitar';
                        toggleButton.classList.remove('bg-gray-600', 'hover:bg-gray-700');
                        toggleButton.classList.add('bg-blue-600', 'hover:bg-blue-700');
                    }
                });
            }
        });
        @endif
    </script>
</x-app-layout>

