<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Relatório - Resumo de Abastecimentos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Botões de Exportação -->
            <x-export-buttons reportName="Resumo de Abastecimentos" routeName="reports.fuelings" :filters="request()->all()" />

            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.fuelings') }}" class="flex gap-4 flex-wrap">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Inicial</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" class="mt-1 block rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Final</label>
                            <input type="date" name="end_date" value="{{ $endDate }}" class="mt-1 block rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Veículo</label>
                            <select name="vehicle_id" class="mt-1 block rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Todos</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ $vehicleId == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Combustível</label>
                            <select name="fuel_type" class="mt-1 block rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Todos</option>
                                @foreach($fuelTypes as $type)
                                    <option value="{{ $type }}" {{ $fuelType == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <x-primary-button type="submit">Filtrar</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Totais -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total de Abastecimentos</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $fuelings->count() }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total de Litros</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($totalLiters, 2, ',', '.') }} L</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Custo Total</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">R$ {{ number_format($totalAmount, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Abastecimentos -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 sortable-table">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 select-none sortable" data-sort="datetime">
                                        Data/Hora
                                        <span class="sort-indicator ml-1"></span>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 select-none sortable" data-sort="text">
                                        Veículo
                                        <span class="sort-indicator ml-1"></span>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 select-none sortable" data-sort="text">
                                        Tipo
                                        <span class="sort-indicator ml-1"></span>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 select-none sortable" data-sort="number">
                                        Litros
                                        <span class="sort-indicator ml-1"></span>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 select-none sortable" data-sort="currency">
                                        Preço/L
                                        <span class="sort-indicator ml-1"></span>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 select-none sortable" data-sort="currency">
                                        Valor Total
                                        <span class="sort-indicator ml-1"></span>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 select-none sortable" data-sort="text">
                                        Posto
                                        <span class="sort-indicator ml-1"></span>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 select-none sortable" data-sort="text">
                                        Usuário
                                        <span class="sort-indicator ml-1"></span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($fuelings as $fueling)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $fueling->date_time->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $fueling->vehicle->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $fueling->fuel_type }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ number_format($fueling->liters, 2, ',', '.') }} L</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">R$ {{ number_format($fueling->price_per_liter, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">R$ {{ number_format($fueling->total_amount, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $fueling->gasStation->name ?? $fueling->gas_station_name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $fueling->user->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum abastecimento encontrado</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-sortable-table-script />
</x-app-layout>

