<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <svg class="w-8 h-8 mr-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <h2 class="font-bold text-2xl text-gray-900 dark:text-gray-100 leading-tight">
                {{ __('Relatórios') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <p class="text-gray-600 dark:text-gray-400 text-lg">
                    Selecione um relatório para visualizar análises detalhadas da sua frota, consumo, manutenções e muito mais.
                </p>
            </div>

            <!-- Reports Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($reports as $report)
                    <a href="{{ route($report['route']) }}" 
                       class="group relative bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 transform hover:-translate-y-1">
                        <!-- Gradient Header -->
                        <div class="h-2 bg-gradient-to-r {{ $report['color'] }}"></div>
                        
                        <!-- Content -->
                        <div class="p-6">
                            <!-- Icon -->
                            <div class="mb-4">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gradient-to-r {{ $report['color'] }} text-white shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $report['icon'] }}"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Title -->
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                {{ $report['title'] }}
                            </h3>

                            <!-- Description -->
                            <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-4">
                                {{ $report['description'] }}
                            </p>

                            <!-- Arrow -->
                            <div class="flex items-center text-indigo-600 dark:text-indigo-400 font-semibold text-sm group-hover:translate-x-1 transition-transform">
                                Acessar relatório
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Hover Effect -->
                        <div class="absolute inset-0 bg-gradient-to-r {{ $report['color'] }} opacity-0 group-hover:opacity-5 transition-opacity duration-300 pointer-events-none"></div>
                    </a>
                @endforeach
            </div>

            <!-- Info Section -->
            <div class="mt-12 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-indigo-200 dark:border-indigo-800">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            Dicas para usar os relatórios
                        </h4>
                        <ul class="text-gray-700 dark:text-gray-300 space-y-1 text-sm">
                            <li>• Use os filtros de data para analisar períodos específicos</li>
                            <li>• Combine múltiplos relatórios para uma visão completa da frota</li>
                            <li>• Exporte os dados quando necessário para análises externas</li>
                            <li>• Monitore regularmente os relatórios de manutenções futuras</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

