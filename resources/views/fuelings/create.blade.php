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
                                    <option value="">Selecione um posto ou digite abaixo</option>
                                    @foreach($gasStations as $gasStation)
                                        <option value="{{ $gasStation->id }}" {{ old('gas_station_id') == $gasStation->id ? 'selected' : '' }}>
                                            {{ $gasStation->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('gas_station_id')" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ou digite um posto personalizado:</p>
                                <x-text-input id="gas_station_name" class="block mt-1 w-full" type="text" name="gas_station_name" :value="old('gas_station_name')" placeholder="Nome do posto (se não estiver na lista)" />
                                <x-input-error :messages="$errors->get('gas_station_name')" class="mt-2" />
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
</x-app-layout>

