<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Novo Percurso') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('trips.store') }}">
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
                                <x-input-label for="driver_id" :value="__('Condutor')" />
                                <div class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <select id="driver_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm bg-gray-100" required disabled>
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->id }}" {{ old('driver_id', auth()->id()) == $driver->id ? 'selected' : '' }}>{{ $driver->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" id="driver_id_hidden" name="driver_id" value="{{ old('driver_id', auth()->id()) }}">
                                        <x-input-error :messages="$errors->get('driver_id')" class="mt-2" />
                                    </div>
                                    <button type="button" id="toggle_driver_select" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm whitespace-nowrap mb-1" title="Habilitar seleção de condutor">
                                        <span id="toggle_driver_text">Habilitar</span>
                                    </button>
                                </div>
                            </div>
                            @elseif(auth()->user()->role === 'condutor')
                            {{-- Condutor sempre usa seu próprio ID, campo oculto --}}
                            <input type="hidden" name="driver_id" value="{{ auth()->id() }}">
                            @else
                            {{-- Outros usuários também usam campo oculto --}}
                            <input type="hidden" name="driver_id" value="{{ auth()->id() }}">
                            @endif

                            <div>
                                <x-input-label for="date" :value="__('Data')" />
                                <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date', date('Y-m-d'))" required />
                                <x-input-error :messages="$errors->get('date')" class="mt-2" />
                            </div>

                            <div>
                                <div class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <x-input-label for="origin_location_id" :value="__('Local de Partida')" />
                                        <select id="origin_location_id" name="origin_location_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                            <option value="">Selecione...</option>
                                            @foreach($locations as $location)
                                                <option value="{{ $location->id }}" {{ old('origin_location_id') == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('origin_location_id')" class="mt-2" />
                                    </div>
                                    <button type="button" onclick="openLocationModal('origin_location_id')" class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-sm whitespace-nowrap" title="Cadastrar novo local">
                                        + Novo
                                    </button>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-end gap-2">
                                    <div class="flex-1">
                                        <x-input-label for="destination_location_id" :value="__('Local de Destino')" />
                                        <select id="destination_location_id" name="destination_location_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                                            <option value="">Selecione...</option>
                                            @foreach($locations as $location)
                                                <option value="{{ $location->id }}" {{ old('destination_location_id') == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('destination_location_id')" class="mt-2" />
                                    </div>
                                    <button type="button" onclick="openLocationModal('destination_location_id')" class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-sm whitespace-nowrap" title="Cadastrar novo local">
                                        + Novo
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="flex items-center mt-6">
                                    <input type="checkbox" name="return_to_origin" value="1" {{ old('return_to_origin') ? 'checked' : '' }} class="rounded border-gray-300">
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Retornou para o local de partida?</span>
                                </label>
                            </div>

                            <div>
                                <x-input-label for="departure_time" :value="__('Horário de Partida')" />
                                <x-text-input id="departure_time" class="block mt-1 w-full" type="time" name="departure_time" :value="old('departure_time')" required />
                                <x-input-error :messages="$errors->get('departure_time')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="return_time" :value="__('Horário de Retorno')" />
                                <x-text-input id="return_time" class="block mt-1 w-full" type="time" name="return_time" :value="old('return_time')" />
                                <x-input-error :messages="$errors->get('return_time')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="odometer_start" :value="__('KM de Saída')" />
                                <x-text-input 
                                    id="odometer_start" 
                                    class="block mt-1 w-full bg-gray-100 dark:bg-gray-700 cursor-not-allowed" 
                                    type="number" 
                                    name="odometer_start" 
                                    :value="old('odometer_start')" 
                                    required 
                                    readonly
                                />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    O KM de Saída é preenchido automaticamente com o odômetro atual do veículo selecionado.
                                </p>
                                <x-input-error :messages="$errors->get('odometer_start')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="odometer_end" :value="__('KM de Chegada')" />
                                <x-text-input id="odometer_end" class="block mt-1 w-full" type="number" name="odometer_end" :value="old('odometer_end')" required />
                                <x-input-error :messages="$errors->get('odometer_end')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="purpose" :value="__('Finalidade / Observações')" />
                                <textarea id="purpose" name="purpose" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">{{ old('purpose') }}</textarea>
                                <x-input-error :messages="$errors->get('purpose')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Paradas Intermediárias -->
                        <div class="mt-8 border-t pt-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                    {{ __('Paradas Intermediárias') }}
                                </h3>
                                <button type="button" onclick="addStop()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                                    + Adicionar Parada
                                </button>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Adicione paradas intermediárias do percurso (ex: Ponto B, Ponto C, etc.). Você pode arrastar e soltar para reordenar.
                            </p>
                            
                            <div id="stops-container" class="space-y-4">
                                <!-- Paradas serão adicionadas aqui dinamicamente -->
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('trips.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <x-primary-button>
                                {{ __('Salvar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cadastro de local -->
    <x-modal name="new-location-modal" maxWidth="2xl">
        <form id="location-form" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ __('Cadastrar Novo Local') }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="modal_name" :value="__('Nome')" />
                    <x-text-input id="modal_name" class="block mt-1 w-full" type="text" name="name" required autofocus />
                    <div id="error_name" class="mt-2"></div>
                </div>

                <div>
                    <x-input-label for="modal_type" :value="__('Tipo')" />
                    <select id="modal_type" name="type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                        <option value="empresa">Empresa</option>
                        <option value="cliente">Cliente</option>
                        <option value="posto_combustivel">Posto de Combustível</option>
                        <option value="outro">Outro</option>
                    </select>
                    <div id="error_type" class="mt-2"></div>
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="modal_address" :value="__('Endereço Completo (opcional)')" />
                    <textarea id="modal_address" name="address" rows="2" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600"></textarea>
                    <div id="error_address" class="mt-2"></div>
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="modal_street" :value="__('Rua')" />
                    <x-text-input id="modal_street" class="block mt-1 w-full" type="text" name="street" />
                    <div id="error_street" class="mt-2"></div>
                </div>

                <div>
                    <x-input-label for="modal_number" :value="__('Número')" />
                    <x-text-input id="modal_number" class="block mt-1 w-full" type="text" name="number" />
                    <div id="error_number" class="mt-2"></div>
                </div>

                <div>
                    <x-input-label for="modal_complement" :value="__('Complemento')" />
                    <x-text-input id="modal_complement" class="block mt-1 w-full" type="text" name="complement" />
                    <div id="error_complement" class="mt-2"></div>
                </div>

                <div>
                    <x-input-label for="modal_neighborhood" :value="__('Bairro')" />
                    <x-text-input id="modal_neighborhood" class="block mt-1 w-full" type="text" name="neighborhood" />
                    <div id="error_neighborhood" class="mt-2"></div>
                </div>

                <div>
                    <x-input-label for="modal_zip_code" :value="__('CEP')" />
                    <x-text-input id="modal_zip_code" class="block mt-1 w-full" type="text" name="zip_code" maxlength="10" placeholder="00000-000" />
                    <div id="error_zip_code" class="mt-2"></div>
                </div>

                <div>
                    <x-input-label for="modal_city" :value="__('Cidade')" />
                    <x-text-input id="modal_city" class="block mt-1 w-full" type="text" name="city" />
                    <div id="error_city" class="mt-2"></div>
                </div>

                <div>
                    <x-input-label for="modal_state" :value="__('Estado (UF)')" />
                    <x-text-input id="modal_state" class="block mt-1 w-full" type="text" name="state" maxlength="2" placeholder="SP" />
                    <div id="error_state" class="mt-2"></div>
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="modal_notes" :value="__('Observações')" />
                    <textarea id="modal_notes" name="notes" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    <div id="error_notes" class="mt-2"></div>
                </div>
            </div>

            <div class="flex items-center justify-end mt-6">
                <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'new-location-modal' }))" class="text-gray-600 hover:text-gray-900 mr-4">
                    Cancelar
                </button>
                <x-primary-button type="submit">
                    {{ __('Salvar') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <!-- SortableJS para drag and drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    
    <style>
        #stops-container > div {
            cursor: default;
        }
        #stops-container > div .cursor-move {
            cursor: move;
            cursor: grab;
        }
        #stops-container > div .cursor-move:active {
            cursor: grabbing;
        }
        #stops-container .sortable-ghost {
            opacity: 0.4;
        }
    </style>
    
    <script>
        let stopIndex = 0;
        let currentSelectId = null;
        let sortableInstance = null;
        let driverSelectEnabled = false;
        const adminUserId = {{ auth()->id() }};
        
        // Controlar habilitação/desabilitação do campo de condutor para admin
        @if(auth()->user()->role === 'admin')
        document.addEventListener('DOMContentLoaded', function() {
            const driverSelect = document.getElementById('driver_id');
            const driverHidden = document.getElementById('driver_id_hidden');
            const toggleButton = document.getElementById('toggle_driver_select');
            const toggleText = document.getElementById('toggle_driver_text');
            
            if (driverSelect && toggleButton && driverHidden) {
                // Sincronizar valor do select com o hidden quando mudar
                driverSelect.addEventListener('change', function() {
                    driverHidden.value = this.value;
                });
                
                toggleButton.addEventListener('click', function() {
                    driverSelectEnabled = !driverSelectEnabled;
                    
                    if (driverSelectEnabled) {
                        // Habilitar campo
                        driverSelect.disabled = false;
                        driverSelect.classList.remove('bg-gray-100');
                        driverSelect.classList.add('bg-white');
                        toggleText.textContent = 'Desabilitar';
                        toggleButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        toggleButton.classList.add('bg-gray-600', 'hover:bg-gray-700');
                    } else {
                        // Desabilitar campo e voltar para admin logado
                        driverSelect.disabled = true;
                        driverSelect.value = adminUserId;
                        driverHidden.value = adminUserId;
                        driverSelect.classList.remove('bg-white');
                        driverSelect.classList.add('bg-gray-100');
                        toggleText.textContent = 'Habilitar';
                        toggleButton.classList.remove('bg-gray-600', 'hover:bg-gray-700');
                        toggleButton.classList.add('bg-blue-600', 'hover:bg-blue-700');
                    }
                });
            }
        });
        @endif

        // Preencher KM de Saída automaticamente ao selecionar veículo
        document.getElementById('vehicle_id').addEventListener('change', function() {
            const vehicleId = this.value;
            const odometerStartField = document.getElementById('odometer_start');
            
            if (vehicleId) {
                // Buscar odômetro atual do veículo
                fetch(`/trips/vehicle/${vehicleId}/odometer`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.current_odometer !== undefined) {
                            odometerStartField.value = data.current_odometer;
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar odômetro do veículo:', error);
                    });
            } else {
                odometerStartField.value = '';
            }
        });

        function addStop() {
            const container = document.getElementById('stops-container');
            const stopDiv = document.createElement('div');
            stopDiv.className = 'border rounded-lg p-4 bg-gray-50 dark:bg-gray-700';
            stopDiv.id = `stop-${stopIndex}`;
            
            stopDiv.innerHTML = `
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-gray-400 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                    </svg>
                    <h4 class="font-medium text-gray-800 dark:text-gray-200 flex-1">Parada <span class="stop-number">${stopIndex + 1}</span></h4>
                    <button type="button" onclick="removeStop(${stopIndex})" class="text-red-600 hover:text-red-800 text-sm">
                        Remover
                    </button>
                </div>
                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Local</label>
                            <select name="stops[${stopIndex}][location_id]" id="stop-${stopIndex}-location" class="block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Selecione...</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" onclick="openLocationModal('stop-${stopIndex}-location')" class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-sm whitespace-nowrap mb-1" title="Cadastrar novo local">
                            + Novo
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(stopDiv);
            stopIndex++;
            updateStopNumbers();
            initializeSortable();
        }

        function updateStopNumbers() {
            const stops = document.querySelectorAll('#stops-container > div');
            stops.forEach((stop, index) => {
                const numberSpan = stop.querySelector('.stop-number');
                if (numberSpan) {
                    numberSpan.textContent = index + 1;
                }
                // Atualizar índices dos campos
                const select = stop.querySelector('select[name^="stops["]');
                if (select) {
                    const oldName = select.name;
                    const newIndex = oldName.match(/\[(\d+)\]/)[1];
                    const newName = oldName.replace(/\[\d+\]/, `[${index}]`);
                    select.name = newName;
                    select.id = `stop-${index}-location`;
                }
            });
        }

        function initializeSortable() {
            const container = document.getElementById('stops-container');
            if (container && !sortableInstance) {
                sortableInstance = new Sortable(container, {
                    animation: 150,
                    handle: 'svg.cursor-move',
                    ghostClass: 'opacity-50',
                    dragClass: 'opacity-50',
                    onEnd: function(evt) {
                        updateStopNumbers();
                    }
                });
            }
        }

        // Inicializar Sortable quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            initializeSortable();
        });

        // Atualizar índices antes de submeter o formulário
        document.querySelector('form')?.addEventListener('submit', function() {
            updateStopNumbers();
        });

        function removeStop(index) {
            const stopDiv = document.getElementById(`stop-${index}`);
            if (stopDiv) {
                stopDiv.remove();
                updateStopNumbers();
            }
        }

        // Função para abrir modal de cadastro de local
        function openLocationModal(selectId) {
            currentSelectId = selectId;
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'new-location-modal' }));
            // Limpar formulário
            document.getElementById('location-form').reset();
            // Limpar erros
            document.querySelectorAll('[id^="error_"]').forEach(el => {
                el.innerHTML = '';
            });
        }

        // Máscara para CEP no modal
        document.getElementById('modal_zip_code')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.substring(0, 5) + '-' + value.substring(5, 8);
            }
            e.target.value = value;
        });

        // Submeter formulário de local via AJAX
        document.getElementById('location-form')?.addEventListener('submit', function(e) {
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

            fetch('{{ route("locations.store-ajax") }}', {
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
                    // Adicionar novo local em todas as selects
                    updateLocationSelects(data.location);
                    
                    // Selecionar o novo local na select que acionou o modal
                    if (currentSelectId) {
                        const select = document.getElementById(currentSelectId);
                        if (select) {
                            select.value = data.location.id;
                        }
                    }
                    
                    // Fechar modal
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'new-location-modal' }));
                    
                    // Limpar formulário
                    this.reset();
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
                        window.showToast('Erro ao cadastrar local. Tente novamente.', 'error');
                    } else {
                        alert('Erro ao cadastrar local. Tente novamente.');
                    }
                }
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        });

        // Função para atualizar todas as selects de local
        function updateLocationSelects(newLocation) {
            const selects = [
                'origin_location_id',
                'destination_location_id',
                ...Array.from(document.querySelectorAll('[id^="stop-"][id$="-location"]')).map(el => el.id)
            ];
            
            selects.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (select) {
                    // Verificar se o local já existe na select
                    const exists = Array.from(select.options).some(option => option.value == newLocation.id);
                    if (!exists) {
                        const option = document.createElement('option');
                        option.value = newLocation.id;
                        option.textContent = newLocation.name;
                        select.appendChild(option);
                    }
                }
            });
        }
    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

