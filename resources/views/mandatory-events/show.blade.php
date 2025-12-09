<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detalhes da Obrigação Legal') }}
            </h2>
            @can('update', $mandatoryEvent)
            <a href="{{ route('mandatory-events.edit', $mandatoryEvent) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Editar
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Informações da Obrigação Legal</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div><strong>Veículo:</strong> {{ $mandatoryEvent->vehicle->name }} ({{ $mandatoryEvent->vehicle->plate }})</div>
                        <div><strong>Tipo:</strong> {{ $mandatoryEvent->type_name }}</div>
                        <div><strong>Data de Vencimento:</strong> {{ $mandatoryEvent->due_date->format('d/m/Y') }}</div>
                        <div><strong>Status:</strong> 
                            @if($mandatoryEvent->resolved)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Pago
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Pendente
                                </span>
                            @endif
                        </div>
                        <div><strong>Notificado:</strong> {{ $mandatoryEvent->notified ? 'Sim' : 'Não' }}</div>
                        @if($mandatoryEvent->description)
                        <div class="col-span-2"><strong>Descrição:</strong> {{ $mandatoryEvent->description }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Status Atual</h3>
                    @php
                        $daysUntilDue = now()->diffInDays($mandatoryEvent->due_date, false);
                        $isOverdue = $mandatoryEvent->due_date < now() && !$mandatoryEvent->resolved;
                        $isUpcoming = $daysUntilDue <= 10 && $daysUntilDue >= 0 && !$mandatoryEvent->resolved;
                    @endphp
                    <div class="grid grid-cols-2 gap-4">
                        <div><strong>Data de Vencimento:</strong> {{ $mandatoryEvent->due_date->format('d/m/Y') }}</div>
                        <div>
                            <strong>Status:</strong>
                            @if($mandatoryEvent->resolved)
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
                        </div>
                        @if(!$mandatoryEvent->resolved)
                            @if($isOverdue)
                            <div class="col-span-2"><strong>Dias Vencidos:</strong> {{ abs($daysUntilDue) }} dia(s)</div>
                            @else
                            <div class="col-span-2"><strong>Dias Restantes:</strong> {{ $daysUntilDue }} dia(s)</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            @if(!$mandatoryEvent->resolved)
            <div class="mt-6">
                @can('update', $mandatoryEvent)
                <form action="{{ route('mandatory-events.resolve', $mandatoryEvent) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" onclick="event.preventDefault(); const form = this.closest('form'); if (typeof handleDelete === 'function') { handleDelete(form, 'Marcar como pago?'); } else { if (confirm('Marcar como pago?')) { form.submit(); } }">
                        Marcar como Pago
                    </button>
                </form>
                @endcan
            </div>
            @endif

            <div class="mt-6">
                <a href="{{ route('mandatory-events.index') }}" class="text-gray-600 hover:text-gray-900">← Voltar para lista</a>
            </div>
        </div>
    </div>
</x-app-layout>

