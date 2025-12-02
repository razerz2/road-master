<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Manutenção') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('maintenances.update', $maintenance) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="vehicle_id" :value="__('Veículo')" />
                                <select id="vehicle_id" name="vehicle_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $maintenance->vehicle_id) == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->name }} - {{ $vehicle->plate }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="date" :value="__('Data')" />
                                <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date', $maintenance->date->format('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="odometer" :value="__('KM no Momento')" />
                                <x-text-input id="odometer" class="block mt-1 w-full" type="number" name="odometer" :value="old('odometer', $maintenance->odometer)" required />
                                <x-input-error :messages="$errors->get('odometer')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="type" :value="__('Tipo de Manutenção (Legado)')" />
                                <select id="type" name="type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="troca_oleo" {{ old('type', $maintenance->type) == 'troca_oleo' ? 'selected' : '' }}>Troca de Óleo</option>
                                    <option value="revisao" {{ old('type', $maintenance->type) == 'revisao' ? 'selected' : '' }}>Revisão</option>
                                    <option value="pneu" {{ old('type', $maintenance->type) == 'pneu' ? 'selected' : '' }}>Pneu</option>
                                    <option value="freio" {{ old('type', $maintenance->type) == 'freio' ? 'selected' : '' }}>Freio</option>
                                    <option value="suspensao" {{ old('type', $maintenance->type) == 'suspensao' ? 'selected' : '' }}>Suspensão</option>
                                    <option value="outro" {{ old('type', $maintenance->type) == 'outro' ? 'selected' : '' }}>Outro</option>
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="maintenance_type_id" :value="__('Tipo de Manutenção')" />
                                <select id="maintenance_type_id" name="maintenance_type_id" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                    <option value="">Selecione...</option>
                                    @foreach($maintenanceTypes as $maintenanceType)
                                        <option value="{{ $maintenanceType->id }}" {{ old('maintenance_type_id', $maintenance->maintenance_type_id) == $maintenanceType->id ? 'selected' : '' }}>
                                            {{ $maintenanceType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('maintenance_type_id')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Descrição')" />
                                <textarea id="description" name="description" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>{{ old('description', $maintenance->description) }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="provider" :value="__('Fornecedor/Oficina')" />
                                <x-text-input id="provider" class="block mt-1 w-full" type="text" name="provider" :value="old('provider', $maintenance->provider)" />
                                <x-input-error :messages="$errors->get('provider')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="cost" :value="__('Custo')" />
                                <x-text-input id="cost" class="block mt-1 w-full" type="number" step="0.01" name="cost" :value="old('cost', $maintenance->cost)" />
                                <x-input-error :messages="$errors->get('cost')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="next_due_date" :value="__('Próxima Manutenção (Data)')" />
                                <x-text-input id="next_due_date" class="block mt-1 w-full" type="date" name="next_due_date" :value="old('next_due_date', $maintenance->next_due_date?->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('next_due_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="next_due_odometer" :value="__('Próxima Manutenção (KM)')" />
                                <x-text-input id="next_due_odometer" class="block mt-1 w-full" type="number" name="next_due_odometer" :value="old('next_due_odometer', $maintenance->next_due_odometer)" />
                                <x-input-error :messages="$errors->get('next_due_odometer')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Observações')" />
                                <textarea id="notes" name="notes" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $maintenance->notes) }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('maintenances.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <x-primary-button>
                                {{ __('Atualizar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

