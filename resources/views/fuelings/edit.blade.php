<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Abastecimento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('fuelings.update', $fueling) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="vehicle_id" :value="__('Veículo')" />
                                <select id="vehicle_id" name="vehicle_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $fueling->vehicle_id) == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->name }} - {{ $vehicle->plate }}</option>
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
                                                <option value="{{ $driver->id }}" {{ old('user_id', $fueling->user_id) == $driver->id ? 'selected' : '' }}>{{ $driver->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" id="user_id_hidden" name="user_id" value="{{ old('user_id', $fueling->user_id) }}">
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
                                <x-text-input id="date_time" class="block mt-1 w-full" type="datetime-local" name="date_time" :value="old('date_time', $fueling->date_time->format('Y-m-d\TH:i'))" required />
                                <x-input-error :messages="$errors->get('date_time')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="odometer" :value="__('KM no Momento')" />
                                <x-text-input id="odometer" class="block mt-1 w-full" type="number" name="odometer" :value="old('odometer', $fueling->odometer)" required />
                                <x-input-error :messages="$errors->get('odometer')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="fuel_type" :value="__('Tipo de Combustível')" />
                                <select id="fuel_type" name="fuel_type" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                                    <option value="">Selecione...</option>
                                    @foreach($fuelTypes as $fuelType)
                                        <option value="{{ $fuelType->name }}" {{ old('fuel_type', $fueling->fuel_type) == $fuelType->name ? 'selected' : '' }}>
                                            {{ $fuelType->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('fuel_type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="liters" :value="__('Litros')" />
                                <x-text-input id="liters" class="block mt-1 w-full" type="number" step="0.01" name="liters" :value="old('liters', $fueling->liters)" required />
                                <x-input-error :messages="$errors->get('liters')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="price_per_liter" :value="__('Preço por Litro')" />
                                <x-text-input id="price_per_liter" class="block mt-1 w-full" type="number" step="0.01" name="price_per_liter" :value="old('price_per_liter', $fueling->price_per_liter)" required />
                                <x-input-error :messages="$errors->get('price_per_liter')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="total_amount" :value="__('Valor Total')" />
                                <x-text-input id="total_amount" class="block mt-1 w-full" type="number" step="0.01" name="total_amount" :value="old('total_amount', $fueling->total_amount)" />
                                <x-input-error :messages="$errors->get('total_amount')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="gas_station_id" :value="__('Posto')" />
                                <div class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <select id="gas_station_id" name="gas_station_id" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                            <option value="">Selecione um posto</option>
                                            @foreach($gasStations as $gasStation)
                                                <option value="{{ $gasStation->id }}" {{ old('gas_station_id', $fueling->gas_station_id) == $gasStation->id ? 'selected' : '' }}>
                                                    {{ $gasStation->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('gas_station_id')" class="mt-2" />
                                    </div>
                                    <button type="button" onclick="openGasStationModal('gas_station_id')" class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-sm whitespace-nowrap mb-1" title="Cadastrar novo posto">
                                        + Novo
                                    </button>
                                </div>
                            </div>

                            <div>
                                <x-input-label for="payment_method_id" :value="__('Forma de Pagamento')" />
                                <select id="payment_method_id" name="payment_method_id" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                    <option value="">Selecione...</option>
                                    @foreach($paymentMethods as $paymentMethod)
                                        <option value="{{ $paymentMethod->id }}" {{ old('payment_method_id', $fueling->payment_method_id) == $paymentMethod->id ? 'selected' : '' }}>
                                            {{ $paymentMethod->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('payment_method_id')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Observações')" />
                                <textarea id="notes" name="notes" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $fueling->notes) }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('fuelings.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <x-primary-button>
                                {{ __('Atualizar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cadastro de posto -->
    <x-modal name="new-gas-station-modal" maxWidth="2xl">
        <form id="gas-station-form" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ __('Cadastrar Novo Posto') }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <x-input-label for="modal_gas_station_name" :value="__('Nome')" />
                    <x-text-input id="modal_gas_station_name" class="block mt-1 w-full" type="text" name="name" required autofocus />
                    <div id="error_name" class="mt-2"></div>
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="modal_gas_station_description" :value="__('Descrição (opcional)')" />
                    <textarea id="modal_gas_station_description" name="description" rows="3" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"></textarea>
                    <div id="error_description" class="mt-2"></div>
                </div>
            </div>

            <div class="flex items-center justify-end mt-6">
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'new-gas-station-modal' }))" class="text-gray-600 hover:text-gray-900 mr-4">
                    Cancelar
                </button>
                <x-primary-button type="submit">
                    {{ __('Salvar') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <script>
        // Função para abrir modal de cadastro de posto
        function openGasStationModal(selectId) {
            currentGasStationSelectId = selectId;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'new-gas-station-modal' }));
            // Limpar formulário
            document.getElementById('gas-station-form').reset();
            // Limpar erros
            document.querySelectorAll('[id^="error_"]').forEach(el => {
                el.innerHTML = '';
            });
        }

        let currentGasStationSelectId = null;

        // Submeter formulário de posto via AJAX
        document.getElementById('gas-station-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Desabilitar botão e mostrar loading
            submitButton.disabled = true;
            submitButton.textContent = 'Salvando...';
            
            // Limpar erros anteriores
            document.querySelectorAll('[id^="error_"]').forEach(el => {
                el.innerHTML = '';
            });

            fetch('{{ route("gas-stations.store-ajax") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(JSON.stringify(data));
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Adicionar novo posto no select
                    updateGasStationSelect(data.gasStation);
                    
                    // Selecionar o novo posto na select que acionou o modal
                    if (currentGasStationSelectId) {
                        const select = document.getElementById(currentGasStationSelectId);
                        if (select) {
                            select.value = data.gasStation.id;
                        }
                    }
                    
                    // Fechar modal
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'new-gas-station-modal' }));
                    
                    // Limpar formulário
                    this.reset();
                    
                    // Mostrar mensagem de sucesso
                    if (window.showToast) {
                        window.showToast(data.message, 'success');
                    } else {
                        alert(data.message);
                    }
                } else {
                    // Mostrar erros de validação
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            const errorElement = document.getElementById(`error_${key}`);
                            if (errorElement) {
                                const errorMessage = data.errors[key][0];
                                errorElement.innerHTML = `
                                    <ul class="mt-1.5 text-sm text-red-600 dark:text-red-400 space-y-1">
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            ${errorMessage}
                                        </li>
                                    </ul>
                                `;
                            }
                        });
                    } else if (data.message) {
                        if (window.showToast) {
                            window.showToast(data.message, 'error');
                        } else {
                            alert(data.message);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                try {
                    const errorData = JSON.parse(error.message);
                    if (errorData.errors) {
                        Object.keys(errorData.errors).forEach(key => {
                            const errorElement = document.getElementById(`error_${key}`);
                            if (errorElement) {
                                const errorMessage = errorData.errors[key][0];
                                errorElement.innerHTML = `
                                    <ul class="mt-1.5 text-sm text-red-600 dark:text-red-400 space-y-1">
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            ${errorMessage}
                                        </li>
                                    </ul>
                                `;
                            }
                        });
                    } else if (errorData.message) {
                        if (window.showToast) {
                            window.showToast(errorData.message, 'error');
                        } else {
                            alert(errorData.message);
                        }
                    }
                } catch (e) {
                    if (window.showToast) {
                        window.showToast('Erro ao cadastrar posto. Tente novamente.', 'error');
                    } else {
                        alert('Erro ao cadastrar posto. Tente novamente.');
                    }
                }
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });

        function updateGasStationSelect(newGasStation) {
            const select = document.getElementById('gas_station_id');
            if (select) {
                // Verificar se o posto já existe na select
                const exists = Array.from(select.options).some(option => option.value == newGasStation.id);
                if (!exists) {
                    const option = document.createElement('option');
                    option.value = newGasStation.id;
                    option.textContent = newGasStation.name;
                    select.appendChild(option);
                }
            }
        }

        // Calcular valor total automaticamente
        let totalAmountManuallyEdited = false;

        function calculateTotalAmount() {
            const liters = parseFloat(document.getElementById('liters').value) || 0;
            const pricePerLiter = parseFloat(document.getElementById('price_per_liter').value) || 0;
            const totalAmountField = document.getElementById('total_amount');
            
            if (liters > 0 && pricePerLiter > 0) {
                const total = liters * pricePerLiter;
                
                // Sempre calcular quando litros ou preço mudarem
                // Se o usuário editou manualmente, ele pode editar novamente depois
                totalAmountField.value = total.toFixed(2);
                totalAmountManuallyEdited = false;
            }
        }

        // Adicionar event listeners para calcular automaticamente
        document.addEventListener('DOMContentLoaded', function() {
            const litersField = document.getElementById('liters');
            const pricePerLiterField = document.getElementById('price_per_liter');
            const totalAmountField = document.getElementById('total_amount');
            
            if (litersField && pricePerLiterField && totalAmountField) {
                // Calcular quando litros ou preço por litro mudarem
                litersField.addEventListener('input', calculateTotalAmount);
                litersField.addEventListener('change', calculateTotalAmount);
                pricePerLiterField.addEventListener('input', calculateTotalAmount);
                pricePerLiterField.addEventListener('change', calculateTotalAmount);
                
                // Permitir que o usuário edite manualmente o valor total
                // Quando ele editar, o valor editado permanece até que ele mude litros ou preço novamente
                totalAmountField.addEventListener('input', function() {
                    totalAmountManuallyEdited = true;
                });
                
                // Calcular inicialmente se os campos já tiverem valores
                if (litersField.value && pricePerLiterField.value) {
                    calculateTotalAmount();
                }
            }
        });

        // Controlar habilitação/desabilitação do campo de usuário para admin
        @if(auth()->user()->role === 'admin')
        document.addEventListener('DOMContentLoaded', function() {
            const userSelect = document.getElementById('user_id');
            const userHidden = document.getElementById('user_id_hidden');
            const toggleButton = document.getElementById('toggle_user_select');
            const toggleText = document.getElementById('toggle_user_text');
            let userSelectEnabled = false;
            const adminUserId = {{ auth()->id() }};
            const currentUserId = {{ $fueling->user_id ?? auth()->id() }};
            
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

