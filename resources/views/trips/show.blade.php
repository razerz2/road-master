<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Detalhes do Percurso') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div><strong>Data:</strong> {{ $trip->date->format('d/m/Y') }}</div>
                        <div><strong>Veículo:</strong> {{ $trip->vehicle->name }} - {{ $trip->vehicle->plate }}</div>
                        <div><strong>Condutor:</strong> {{ $trip->driver->name }}</div>
                        <div><strong>Rota:</strong> 
                            {{ $trip->originLocation->name }}
                            @if($trip->stops->count() > 0)
                                @foreach($trip->stops as $stop)
                                    → {{ $stop->location->name }}
                                @endforeach
                            @endif
                            → {{ $trip->destinationLocation->name }}
                            @if($trip->return_to_origin)
                                → {{ $trip->originLocation->name }}
                            @endif
                        </div>
                        <div><strong>Retorno:</strong> {{ $trip->return_to_origin ? 'Sim' : 'Não' }}</div>
                        <div><strong>Horário de Partida:</strong> {{ $trip->departure_time }}</div>
                        <div><strong>Horário de Retorno:</strong> {{ $trip->return_time ?? '-' }}</div>
                        <div><strong>KM de Saída:</strong> {{ number_format($trip->odometer_start, 0, ',', '.') }} km</div>
                        <div><strong>KM de Chegada:</strong> {{ number_format($trip->odometer_end, 0, ',', '.') }} km</div>
                        <div><strong>KM Total:</strong> {{ number_format($trip->km_total, 0, ',', '.') }} km</div>
                        @if($trip->purpose)
                        <div class="md:col-span-2"><strong>Finalidade:</strong> {{ $trip->purpose }}</div>
                        @endif
                    </div>

                    @if($trip->stops->count() > 0)
                    <div class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                            Paradas Intermediárias
                        </h3>
                        <div class="space-y-3">
                            @foreach($trip->stops as $stop)
                            <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
                                <div>
                                    <strong class="text-sm text-gray-600 dark:text-gray-400">Parada {{ $stop->sequence }}:</strong>
                                    <div class="font-medium mt-1">{{ $stop->location->name }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    <div class="flex items-center justify-end mt-6">
                        @can('update', $trip)
                        <a href="{{ route('trips.edit', $trip) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">Editar</a>
                        @endcan
                        <a href="{{ route('trips.index') }}" class="text-gray-600 hover:text-gray-900">← Voltar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

