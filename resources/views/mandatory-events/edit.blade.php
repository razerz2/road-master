<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Obrigação Legal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($mandatoryEvent->resolved)
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                    <strong>Atenção:</strong> Esta obrigatoriedade já foi marcada como paga e não pode ser editada. Apenas visualização e exclusão são permitidas.
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($mandatoryEvent->resolved)
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Esta obrigatoriedade está marcada como paga e não pode ser editada. Os campos abaixo estão desabilitados apenas para visualização.
                            </p>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('mandatory-events.update', $mandatoryEvent) }}" onsubmit="@if($mandatoryEvent->resolved) event.preventDefault(); alert('Esta obrigatoriedade está marcada como paga e não pode ser editada.'); return false; @endif">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="vehicle_id" :value="__('Veículo')" />
                                <select id="vehicle_id" name="vehicle_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm {{ $mandatoryEvent->resolved ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ $mandatoryEvent->resolved ? 'disabled' : 'required' }}>
                                    <option value="">Selecione...</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $mandatoryEvent->vehicle_id) == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->name }} - {{ $vehicle->plate }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="type" :value="__('Tipo de Obrigação')" />
                                <select id="type" name="type" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm {{ $mandatoryEvent->resolved ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ $mandatoryEvent->resolved ? 'disabled' : 'required' }}>
                                    <option value="">Selecione...</option>
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}" {{ old('type', $mandatoryEvent->type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="due_date" :value="__('Data de Vencimento')" />
                                <x-text-input id="due_date" class="block mt-1 w-full {{ $mandatoryEvent->resolved ? 'bg-gray-100 cursor-not-allowed' : '' }}" type="date" name="due_date" :value="old('due_date', $mandatoryEvent->due_date->format('Y-m-d'))" {{ $mandatoryEvent->resolved ? 'disabled' : 'required' }} />
                                <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Descrição (Opcional)')" />
                                <textarea id="description" name="description" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm {{ $mandatoryEvent->resolved ? 'bg-gray-100 cursor-not-allowed' : '' }}" rows="3" {{ $mandatoryEvent->resolved ? 'disabled' : '' }}>{{ old('description', $mandatoryEvent->description) }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="recurring" value="1" {{ old('recurring', $mandatoryEvent->recurring) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 {{ $mandatoryEvent->resolved ? 'cursor-not-allowed opacity-50' : '' }}" {{ $mandatoryEvent->resolved ? 'disabled' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                        {{ __('Criar automaticamente próxima ocorrência ao marcar como paga') }}
                                    </span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('Quando marcado, ao marcar esta obrigação como paga, será criada automaticamente uma nova ocorrência para o próximo ano (válido apenas para IPVA e Licenciamento).') }}
                                </p>
                                <x-input-error :messages="$errors->get('recurring')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <strong>Status:</strong> 
                                        @if($mandatoryEvent->resolved)
                                            <span class="text-green-600">Pago</span>
                                        @else
                                            <span class="text-red-600">Pendente</span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                        <strong>Notificado:</strong> 
                                        {{ $mandatoryEvent->notified ? 'Sim' : 'Não' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('mandatory-events.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            @if(!$mandatoryEvent->resolved)
                            <x-primary-button>
                                {{ __('Atualizar') }}
                            </x-primary-button>
                            @else
                            <a href="{{ route('mandatory-events.show', $mandatoryEvent) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Voltar para Visualização
                            </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

