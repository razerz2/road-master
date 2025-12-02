<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Revisão') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('review-notifications.update', $reviewNotification) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="vehicle_id" :value="__('Veículo')" />
                                <select id="vehicle_id" name="vehicle_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required onchange="updateCurrentKm(this)">
                                    <option value="">Selecione...</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" data-odometer="{{ $vehicle->current_odometer ?? 0 }}" {{ old('vehicle_id', $reviewNotification->vehicle_id) == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->name }} - {{ $vehicle->plate }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="review_type" :value="__('Tipo de Revisão')" />
                                <select id="review_type" name="review_type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Selecione...</option>
                                    @foreach($reviewTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('review_type', $reviewNotification->review_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('review_type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="name" :value="__('Nome Personalizado (Opcional)')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $reviewNotification->name)" placeholder="Ex: Troca de óleo 10W40" />
                                <p class="mt-1 text-sm text-gray-500">Deixe em branco para usar o nome padrão do tipo</p>
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="current_km" :value="__('KM Atual do Veículo')" />
                                <x-text-input id="current_km" class="block mt-1 w-full" type="number" name="current_km" :value="old('current_km', $reviewNotification->current_km)" min="0" />
                                <p class="mt-1 text-sm text-gray-500">Deixe em branco para usar o odômetro atual do veículo</p>
                                <x-input-error :messages="$errors->get('current_km')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="notification_km" :value="__('KM para Notificação')" />
                                <x-text-input id="notification_km" class="block mt-1 w-full" type="number" name="notification_km" :value="old('notification_km', $reviewNotification->notification_km)" min="0" required />
                                <p class="mt-1 text-sm text-gray-500">KM onde a notificação será disparada</p>
                                <x-input-error :messages="$errors->get('notification_km')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Descrição (Opcional)')" />
                                <textarea id="description" name="description" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" rows="3">{{ old('description', $reviewNotification->description) }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="active" value="1" {{ old('active', $reviewNotification->active) ? 'checked' : '' }} class="rounded border-gray-300">
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Ativo</span>
                                </label>
                                <x-input-error :messages="$errors->get('active')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('review-notifications.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
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
        function updateCurrentKm(select) {
            const option = select.options[select.selectedIndex];
            const odometer = option.getAttribute('data-odometer');
            const currentKmInput = document.getElementById('current_km');
            if (odometer && !currentKmInput.value) {
                currentKmInput.value = odometer;
            }
        }
    </script>
</x-app-layout>

