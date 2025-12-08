<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Relatório - Manutenções Futuras') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Botões de Exportação -->
            <x-export-buttons reportName="Manutenções Futuras" routeName="reports.upcoming-maintenance" :filters="request()->all()" />

            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('reports.upcoming-maintenance') }}" class="flex gap-4 flex-wrap">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Veículo</label>
                            <select name="vehicle_id" class="mt-1 block rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Todos</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ $vehicleId == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <x-primary-button type="submit">Filtrar</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Manutenções Atrasadas -->
            @if(count($overdue) > 0)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-4">⚠️ Manutenções Atrasadas</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-red-200 dark:divide-red-800">
                            <thead class="bg-red-100 dark:bg-red-900/40">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-red-200 uppercase">Veículo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-red-200 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-red-200 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-red-800 dark:text-red-200 uppercase">Detalhes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-red-200 dark:divide-red-800">
                                @foreach($overdue as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $item['maintenance']->vehicle->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $item['maintenance']->type)) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 dark:text-red-400">Atrasada</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            @if($item['type'] == 'date')
                                                {{ $item['days_overdue'] }} dias atrasada
                                            @else
                                                {{ number_format($item['km_overdue'], 0, ',', '.') }} km atrasada
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Próximas por Data -->
            @if(count($upcomingByDate) > 0)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-4">📅 Próximas Manutenções por Data (próximos 30 dias)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-yellow-200 dark:divide-yellow-800">
                            <thead class="bg-yellow-100 dark:bg-yellow-900/40">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-yellow-800 dark:text-yellow-200 uppercase">Veículo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-yellow-800 dark:text-yellow-200 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-yellow-800 dark:text-yellow-200 uppercase">Data Prevista</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-yellow-800 dark:text-yellow-200 uppercase">Dias Restantes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-yellow-200 dark:divide-yellow-800">
                                @foreach($upcomingByDate as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $item['maintenance']->vehicle->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $item['maintenance']->type)) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $item['maintenance']->next_due_date->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-yellow-600 dark:text-yellow-400">{{ $item['days_until'] }} dias</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Próximas por KM -->
            @if(count($upcomingByKm) > 0)
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-4">🛣️ Próximas Manutenções por KM (próximos 1000 km)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-blue-200 dark:divide-blue-800">
                            <thead class="bg-blue-100 dark:bg-blue-900/40">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 dark:text-blue-200 uppercase">Veículo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 dark:text-blue-200 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 dark:text-blue-200 uppercase">KM Atual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 dark:text-blue-200 uppercase">KM Previsto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-800 dark:text-blue-200 uppercase">KM Restantes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-blue-200 dark:divide-blue-800">
                                @foreach($upcomingByKm as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $item['maintenance']->vehicle->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $item['maintenance']->type)) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ number_format($item['current_km'], 0, ',', '.') }} km</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ number_format($item['maintenance']->next_due_odometer, 0, ',', '.') }} km</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600 dark:text-blue-400">{{ number_format($item['km_until'], 0, ',', '.') }} km</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @if(count($overdue) == 0 && count($upcomingByDate) == 0 && count($upcomingByKm) == 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                    Nenhuma manutenção futura encontrada
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

