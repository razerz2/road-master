<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Novo Veículo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('vehicles.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Nome')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="plate" :value="__('Placa')" />
                                <x-text-input id="plate" class="block mt-1 w-full" type="text" name="plate" :value="old('plate')" required />
                                <x-input-error :messages="$errors->get('plate')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="brand" :value="__('Marca')" />
                                <x-text-input id="brand" class="block mt-1 w-full" type="text" name="brand" :value="old('brand')" />
                                <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="model" :value="__('Modelo')" />
                                <x-text-input id="model" class="block mt-1 w-full" type="text" name="model" :value="old('model')" />
                                <x-input-error :messages="$errors->get('model')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="year" :value="__('Ano')" />
                                <x-text-input id="year" class="block mt-1 w-full" type="number" name="year" :value="old('year')" />
                                <x-input-error :messages="$errors->get('year')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="fuel_type_id" :value="__('Tipo de Combustível')" />
                                <select id="fuel_type_id" name="fuel_type_id" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                    <option value="">Selecione...</option>
                                    @foreach($fuelTypes as $fuelType)
                                        <option value="{{ $fuelType->id }}" {{ old('fuel_type_id') == $fuelType->id ? 'selected' : '' }}>
                                            {{ $fuelType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('fuel_type_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="tank_capacity" :value="__('Capacidade do Tanque (L)')" />
                                <x-text-input id="tank_capacity" class="block mt-1 w-full" type="number" step="0.01" name="tank_capacity" :value="old('tank_capacity')" />
                                <x-input-error :messages="$errors->get('tank_capacity')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="current_odometer" :value="__('Odômetro Atual (km)')" />
                                <x-text-input id="current_odometer" class="block mt-1 w-full" type="number" name="current_odometer" :value="old('current_odometer', 0)" />
                                <x-input-error :messages="$errors->get('current_odometer')" class="mt-2" />
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }} class="rounded border-gray-300">
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Ativo</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('vehicles.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
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

