<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detalhes do Veículo') }}
            </h2>
            @can('update', $vehicle)
            <a href="{{ route('vehicles.edit', $vehicle) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Editar
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Informações Básicas</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div><strong>Nome:</strong> {{ $vehicle->name }}</div>
                        <div><strong>Placa:</strong> {{ $vehicle->plate }}</div>
                        <div><strong>Marca:</strong> {{ $vehicle->brand ?? '-' }}</div>
                        <div><strong>Modelo:</strong> {{ $vehicle->model ?? '-' }}</div>
                        <div><strong>Ano:</strong> {{ $vehicle->year ?? '-' }}</div>
                        <div><strong>Tipos de Combustível:</strong> 
                            @if($vehicle->fuelTypes->count() > 0)
                                {{ $vehicle->fuelTypes->pluck('name')->join(', ') }}
                            @else
                                -
                            @endif
                        </div>
                        <div><strong>Capacidade do Tanque:</strong> {{ $vehicle->tank_capacity ? number_format($vehicle->tank_capacity, 2, ',', '.') . ' L' : '-' }}</div>
                        @if($vehicle->km_inicial)
                        <div><strong>KM Inicial:</strong> {{ number_format($vehicle->km_inicial, 0, ',', '.') }} km</div>
                        @endif
                        <div><strong>Odômetro Atual:</strong> {{ number_format($vehicle->current_odometer, 0, ',', '.') }} km</div>
                        @if($vehicle->km_inicial)
                        <div><strong>KM Rodados Total:</strong> {{ number_format($vehicle->current_odometer - $vehicle->km_inicial, 0, ',', '.') }} km</div>
                        @endif
                        <div><strong>Status:</strong> 
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $vehicle->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $vehicle->active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Últimos Percursos -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Últimos Percursos</h3>
                        @forelse($vehicle->trips as $trip)
                            <div class="mb-2 text-sm">
                                <div>{{ $trip->date->format('d/m/Y') }} - {{ number_format($trip->km_total, 0, ',', '.') }} km</div>
                                <div class="text-gray-500">{{ $trip->originLocation->name }} → {{ $trip->destinationLocation->name }}</div>
                            </div>
                        @empty
                            <p class="text-gray-500">Nenhum percurso registrado</p>
                        @endforelse
                    </div>
                </div>

                <!-- Últimos Abastecimentos -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Últimos Abastecimentos</h3>
                        @forelse($vehicle->fuelings as $fueling)
                            <div class="mb-2 text-sm">
                                <div>{{ $fueling->date_time->format('d/m/Y H:i') }}</div>
                                <div class="text-gray-500">{{ number_format($fueling->liters, 2, ',', '.') }} L - R$ {{ number_format($fueling->total_amount, 2, ',', '.') }}</div>
                            </div>
                        @empty
                            <p class="text-gray-500">Nenhum abastecimento registrado</p>
                        @endforelse
                    </div>
                </div>

                <!-- Últimas Manutenções -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Últimas Manutenções</h3>
                        @forelse($vehicle->maintenances as $maintenance)
                            <div class="mb-2 text-sm">
                                <div>{{ $maintenance->date->format('d/m/Y') }} - {{ ucfirst(str_replace('_', ' ', $maintenance->type)) }}</div>
                                <div class="text-gray-500">{{ $maintenance->cost ? 'R$ ' . number_format($maintenance->cost, 2, ',', '.') : '-' }}</div>
                            </div>
                        @empty
                            <p class="text-gray-500">Nenhuma manutenção registrada</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('vehicles.index') }}" class="text-blue-600 hover:text-blue-900">← Voltar para lista</a>
            </div>
        </div>
    </div>
</x-app-layout>

