<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detalhes do Local') }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('locations.edit', $location) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
                <a href="{{ route('locations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Informações Básicas -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Informações Básicas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nome</p>
                            <p class="text-base text-gray-900 dark:text-gray-100 font-medium">{{ $location->name }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tipo</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                @if($location->type === 'empresa')
                                    Empresa
                                @elseif($location->type === 'cliente')
                                    Cliente
                                @elseif($location->type === 'posto_combustivel')
                                    Posto de Combustível
                                @else
                                    Outro
                                @endif
                            </span>
                        </div>

                        @if($location->created_at)
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Data de Cadastro</p>
                            <p class="text-base text-gray-900 dark:text-gray-100 font-medium">
                                {{ $location->created_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        @endif

                        @if($location->updated_at && $location->updated_at->ne($location->created_at))
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Última Atualização</p>
                            <p class="text-base text-gray-900 dark:text-gray-100 font-medium">
                                {{ $location->updated_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Endereço
                    </h3>
                    
                    @if($location->address || $location->street || $location->number || $location->complement || $location->neighborhood || $location->zip_code || $location->city || $location->state)
                        @if($location->address)
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Endereço Completo</p>
                                <p class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $location->address }}</p>
                            </div>
                        @endif

                        @if($location->street || $location->number || $location->complement || $location->neighborhood || $location->zip_code || $location->city || $location->state)
                            <div class="space-y-2">
                                @if($location->street || $location->number || $location->complement)
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Logradouro</p>
                                        <p class="text-base text-gray-900 dark:text-gray-100">
                                            @if($location->street)
                                                {{ $location->street }}
                                                @if($location->number)
                                                    , {{ $location->number }}
                                                @endif
                                                @if($location->complement)
                                                    - {{ $location->complement }}
                                                @endif
                                            @elseif($location->number)
                                                Número: {{ $location->number }}
                                            @endif
                                        </p>
                                    </div>
                                @endif

                                @if($location->neighborhood)
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Bairro</p>
                                        <p class="text-base text-gray-900 dark:text-gray-100">{{ $location->neighborhood }}</p>
                                    </div>
                                @endif

                                @if($location->city || $location->state)
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Cidade / Estado</p>
                                        <p class="text-base text-gray-900 dark:text-gray-100">
                                            @if($location->city)
                                                {{ $location->city }}
                                            @endif
                                            @if($location->city && $location->state)
                                                /
                                            @endif
                                            @if($location->state)
                                                {{ strtoupper($location->state) }}
                                            @endif
                                        </p>
                                    </div>
                                @endif

                                @if($location->zip_code)
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">CEP</p>
                                        <p class="text-base text-gray-900 dark:text-gray-100">{{ $location->zip_code }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="flex items-center text-gray-500 dark:text-gray-400">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm italic">Endereço do local não foi informado.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Observações -->
            @if($location->notes)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Observações
                    </h3>
                    <p class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $location->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

