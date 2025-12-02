<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Local') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('locations.update', $location) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Nome')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $location->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="type" :value="__('Tipo (Legado)')" />
                                <select id="type" name="type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="empresa" {{ old('type', $location->type) == 'empresa' ? 'selected' : '' }}>Empresa</option>
                                    <option value="cliente" {{ old('type', $location->type) == 'cliente' ? 'selected' : '' }}>Cliente</option>
                                    <option value="posto_combustivel" {{ old('type', $location->type) == 'posto_combustivel' ? 'selected' : '' }}>Posto de Combustível</option>
                                    <option value="outro" {{ old('type', $location->type) == 'outro' ? 'selected' : '' }}>Outro</option>
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="location_type_id" :value="__('Tipo de Local')" />
                                <select id="location_type_id" name="location_type_id" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                    <option value="">Selecione...</option>
                                    @foreach($locationTypes as $locationType)
                                        <option value="{{ $locationType->id }}" {{ old('location_type_id', $location->location_type_id) == $locationType->id ? 'selected' : '' }}>
                                            {{ $locationType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('location_type_id')" class="mt-2" />
                            </div>

                            <!-- Seção de Endereço -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Endereço
                                </h3>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="address" :value="__('Endereço Completo (opcional)')" />
                                <textarea id="address" name="address" rows="2" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600">{{ old('address', $location->address) }}</textarea>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use este campo se preferir informar o endereço completo em texto livre</p>
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ou preencha os campos detalhados abaixo:</p>
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="street" :value="__('Rua')" />
                                <x-text-input id="street" class="block mt-1 w-full" type="text" name="street" :value="old('street', $location->street)" />
                                <x-input-error :messages="$errors->get('street')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="number" :value="__('Número')" />
                                <x-text-input id="number" class="block mt-1 w-full" type="text" name="number" :value="old('number', $location->number)" />
                                <x-input-error :messages="$errors->get('number')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="complement" :value="__('Complemento')" />
                                <x-text-input id="complement" class="block mt-1 w-full" type="text" name="complement" :value="old('complement', $location->complement)" />
                                <x-input-error :messages="$errors->get('complement')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="neighborhood" :value="__('Bairro')" />
                                <x-text-input id="neighborhood" class="block mt-1 w-full" type="text" name="neighborhood" :value="old('neighborhood', $location->neighborhood)" />
                                <x-input-error :messages="$errors->get('neighborhood')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="zip_code" :value="__('CEP')" />
                                <x-text-input id="zip_code" class="block mt-1 w-full" type="text" name="zip_code" :value="old('zip_code', $location->zip_code)" maxlength="10" placeholder="00000-000" />
                                <x-input-error :messages="$errors->get('zip_code')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="city" :value="__('Cidade')" />
                                <x-text-input id="city" class="block mt-1 w-full" type="text" name="city" :value="old('city', $location->city)" />
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="state" :value="__('Estado (UF)')" />
                                <x-text-input id="state" class="block mt-1 w-full" type="text" name="state" :value="old('state', $location->state)" maxlength="2" placeholder="SP" />
                                <x-input-error :messages="$errors->get('state')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Observações')" />
                                <textarea id="notes" name="notes" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $location->notes) }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('locations.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <x-primary-button>
                                {{ __('Atualizar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Máscara para CEP
        document.addEventListener('DOMContentLoaded', function() {
            const zipCodeInput = document.getElementById('zip_code');
            if (zipCodeInput) {
                zipCodeInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length > 5) {
                        value = value.substring(0, 5) + '-' + value.substring(5, 8);
                    }
                    e.target.value = value;
                });
            }
        });
    </script>
</x-app-layout>

