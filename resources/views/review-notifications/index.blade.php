<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Revisão') }}
            </h2>
            @can('create', App\Models\ReviewNotification::class)
            <a href="{{ route('review-notifications.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Nova Revisão
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('review-notifications.index') }}" class="flex gap-4 flex-wrap">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Veículo</label>
                            <select name="vehicle_id" class="mt-1 block rounded-md border-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Revisão</label>
                            <select name="review_type" class="mt-1 block rounded-md border-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                @foreach($reviewTypes as $key => $label)
                                    <option value="{{ $key }}" {{ request('review_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select name="active" class="mt-1 block rounded-md border-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <x-primary-button type="submit">Filtrar</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Veículo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo de Revisão</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">KM Atual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">KM Notificação</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($notifications as $notification)
                                    @php
                                        $vehicle = $notification->vehicle;
                                        $currentOdometer = $vehicle->current_odometer ?? 0;
                                        $kmRemaining = $notification->notification_km - $currentOdometer;
                                        $isOverdue = $currentOdometer >= $notification->notification_km;
                                    @endphp
                                    <tr class="{{ $isOverdue ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $vehicle->name }}<br>
                                            <span class="text-xs text-gray-500">{{ $vehicle->plate }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $notification->name ?: $notification->review_type_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ number_format($currentOdometer, 0, ',', '.') }} km
                                            @if($isOverdue)
                                                <span class="text-red-600 font-semibold">(Atrasado)</span>
                                            @elseif($kmRemaining <= 1000)
                                                <span class="text-orange-600 font-semibold">(Próximo)</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ number_format($notification->notification_km, 0, ',', '.') }} km
                                            @if(!$isOverdue)
                                                <br><span class="text-xs text-gray-500">Faltam {{ number_format($kmRemaining, 0, ',', '.') }} km</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $notification->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $notification->active ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @can('update', $notification)
                                            <a href="{{ route('review-notifications.edit', $notification) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Editar</a>
                                            <form action="{{ route('review-notifications.toggle-active', $notification) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-blue-600 hover:text-blue-900 mr-2">
                                                    {{ $notification->active ? 'Desativar' : 'Ativar' }}
                                                </button>
                                            </form>
                                            @endcan
                                            @can('delete', $notification)
                                            <form action="{{ route('review-notifications.destroy', $notification) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza?')">Excluir</button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma revisão encontrada</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

