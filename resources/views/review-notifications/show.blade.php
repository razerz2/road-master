<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detalhes da Revisão') }}
            </h2>
            @can('update', $reviewNotification)
            @if(!$reviewNotification->completed_at)
                <form action="{{ route('review-notifications.mark-completed', $reviewNotification) }}" method="POST" class="inline mr-2">
                    @csrf
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" onclick="event.preventDefault(); const form = this.closest('form'); if (typeof handleConfirm === 'function') { handleConfirm(form, 'Deseja marcar esta revisão como realizada?', 'Marcar como Realizada'); } else { if (confirm('Deseja marcar esta revisão como realizada?')) { form.submit(); } }">
                        Marcar como Realizada
                    </button>
                </form>
            @endif
            <a href="{{ route('review-notifications.edit', $reviewNotification) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Editar
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Informações da Revisão</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div><strong>Veículo:</strong> {{ $reviewNotification->vehicle->name }} ({{ $reviewNotification->vehicle->plate }})</div>
                        <div><strong>Tipo de Revisão:</strong> {{ $reviewNotification->review_type_name }}</div>
                        @if($reviewNotification->name)
                        <div><strong>Nome Personalizado:</strong> {{ $reviewNotification->name }}</div>
                        @endif
                        <div><strong>KM Atual (Configurado):</strong> {{ number_format($reviewNotification->current_km, 0, ',', '.') }} km</div>
                        <div><strong>KM para Notificação:</strong> {{ number_format($reviewNotification->notification_km, 0, ',', '.') }} km</div>
                        @if($reviewNotification->last_notified_km)
                        <div><strong>Último KM Notificado:</strong> {{ number_format($reviewNotification->last_notified_km, 0, ',', '.') }} km</div>
                        @endif
                        @if($reviewNotification->last_notified_at)
                        <div><strong>Última Notificação:</strong> {{ $reviewNotification->last_notified_at->format('d/m/Y H:i') }}</div>
                        @endif
                        <div><strong>Status:</strong> 
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $reviewNotification->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $reviewNotification->active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                        <div><strong>Realizada:</strong> 
                            @if($reviewNotification->completed_at)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Sim - {{ $reviewNotification->completed_at->format('d/m/Y H:i') }}
                                </span>
                                @if($reviewNotification->completed_km)
                                    <br><span class="text-sm text-gray-600">KM: {{ number_format($reviewNotification->completed_km, 0, ',', '.') }}</span>
                                @endif
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Não
                                </span>
                            @endif
                        </div>
                        @if($reviewNotification->description)
                        <div class="col-span-2"><strong>Descrição:</strong> {{ $reviewNotification->description }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Status Atual do Veículo</h3>
                    @php
                        $vehicle = $reviewNotification->vehicle;
                        $currentOdometer = $vehicle->current_odometer ?? 0;
                        $kmRemaining = $reviewNotification->notification_km - $currentOdometer;
                        $isOverdue = $currentOdometer >= $reviewNotification->notification_km;
                    @endphp
                    <div class="grid grid-cols-2 gap-4">
                        <div><strong>Odômetro Atual:</strong> {{ number_format($currentOdometer, 0, ',', '.') }} km</div>
                        <div>
                            <strong>Status:</strong>
                            @if($isOverdue)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Atrasado
                                </span>
                            @elseif($kmRemaining <= 1000)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">
                                    Próximo
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Em dia
                                </span>
                            @endif
                        </div>
                        @if(!$isOverdue)
                        <div class="col-span-2"><strong>KM Restantes:</strong> {{ number_format($kmRemaining, 0, ',', '.') }} km</div>
                        @else
                        <div class="col-span-2"><strong>KM Excedido:</strong> {{ number_format(abs($kmRemaining), 0, ',', '.') }} km</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('review-notifications.index') }}" class="text-gray-600 hover:text-gray-900">← Voltar para lista</a>
            </div>
        </div>
    </div>
</x-app-layout>

