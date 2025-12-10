<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Obrigações Legais') }}
            </h2>
            @can('create', App\Models\VehicleMandatoryEvent::class)
            <a href="{{ route('mandatory-events.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Nova Obrigação
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
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('mandatory-events.index') }}" class="flex gap-4 flex-wrap">
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo</label>
                            <select name="type" class="mt-1 block rounded-md border-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select name="resolved" class="mt-1 block rounded-md border-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                <option value="0" {{ request('resolved') === '0' ? 'selected' : '' }}>Pendente</option>
                                <option value="1" {{ request('resolved') === '1' ? 'selected' : '' }}>Pago</option>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data de Vencimento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($events as $event)
                                    @php
                                        $vehicle = $event->vehicle;
                                        $daysUntilDue = (int) now()->diffInDays($event->due_date, false);
                                        $isOverdue = $event->due_date < now() && !$event->resolved;
                                        $isUpcoming = $daysUntilDue <= 10 && $daysUntilDue >= 0 && !$event->resolved;
                                    @endphp
                                    <tr class="{{ $isOverdue ? 'bg-red-50 dark:bg-red-900/20' : ($isUpcoming ? 'bg-orange-50 dark:bg-orange-900/20' : '') }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $vehicle->name }}<br>
                                            <span class="text-xs text-gray-500">{{ $vehicle->plate }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $event->type_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $event->due_date->format('d/m/Y') }}
                                            @if(!$event->resolved)
                                                @if($isOverdue)
                                                    <br><span class="text-xs text-red-600 font-semibold">Vencido há {{ abs($daysUntilDue) }} dia(s)</span>
                                                @elseif($isUpcoming)
                                                    <br><span class="text-xs text-orange-600 font-semibold">Vence em {{ $daysUntilDue }} dia(s)</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($event->resolved)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Pago
                                                </span>
                                            @elseif($isOverdue)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Vencido
                                                </span>
                                            @elseif($isUpcoming)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                                    Próximo
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Em dia
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if(!$event->resolved)
                                                @can('update', $event)
                                                <form action="{{ route('mandatory-events.resolve', $event) }}" method="POST" class="inline mr-2">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900" onclick="event.preventDefault(); const form = this.closest('form'); if (typeof handleConfirm === 'function') { handleConfirm(form, 'Deseja marcar esta obrigatoriedade como paga?', 'Marcar como Pago'); } else { if (confirm('Deseja marcar esta obrigatoriedade como paga?')) { form.submit(); } }">
                                                        Marcar como Pago
                                                    </button>
                                                </form>
                                                @endcan
                                            @endif
                                            @can('update', $event)
                                            @if(!$event->resolved)
                                            <a href="{{ route('mandatory-events.edit', $event) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Editar</a>
                                            @endif
                                            @endcan
                                            @can('delete', $event)
                                            <form action="{{ route('mandatory-events.destroy', $event) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="event.preventDefault(); const form = this.closest('form'); if (typeof handleDelete === 'function') { handleDelete(form, 'Tem certeza?'); } else { if (confirm('Tem certeza?')) { form.submit(); } }">Excluir</button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma obrigação encontrada</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $events->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

