<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Locais') }}
            </h2>
            <a href="{{ route('locations.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Novo Local
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Campo de Busca -->
                    <div class="mb-6">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="searchInput" 
                                placeholder="Buscar por nome, tipo, cidade ou estado..." 
                                value="{{ request('search') }}"
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150"
                            />
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cidade/Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($locations as $location)
                                    <tr class="location-row hover:bg-gray-50 dark:hover:bg-gray-700/50 transition" 
                                        data-name="{{ strtolower($location->name) }}"
                                        data-type="{{ strtolower(str_replace('_', ' ', $location->type)) }}"
                                        data-city="{{ strtolower($location->city ?? '') }}"
                                        data-state="{{ strtolower($location->state ?? '') }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="{{ route('locations.show', $location) }}" class="text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium">
                                                {{ $location->name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' ', $location->type)) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            @if($location->city || $location->state)
                                                {{ $location->city ?? '-' }}{{ $location->city && $location->state ? '/' : '' }}{{ $location->state ? strtoupper($location->state) : '' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('locations.show', $location) }}" class="text-blue-600 hover:text-blue-900 mr-2" title="Visualizar">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('locations.edit', $location) }}" class="text-indigo-600 hover:text-indigo-900 mr-2" title="Editar">
                                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('locations.destroy', $location) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza que deseja excluir este local?')" title="Excluir">
                                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum local cadastrado</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($locations->hasPages())
                        <div class="mt-4 px-6 pb-6">
                            {{ $locations->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableBody = document.getElementById('tableBody');
            const rows = tableBody.querySelectorAll('.location-row');
            let searchTimeout;
            let serverSearchTimeout;

            // Filtro local (rápido) na página atual
            function filterTableLocal() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                
                rows.forEach(row => {
                    const name = row.getAttribute('data-name') || '';
                    const type = row.getAttribute('data-type') || '';
                    const city = row.getAttribute('data-city') || '';
                    const state = row.getAttribute('data-state') || '';
                    
                    const matches = name.includes(searchTerm) || 
                                  type.includes(searchTerm) || 
                                  city.includes(searchTerm) || 
                                  state.includes(searchTerm);
                    
                    row.style.display = matches ? '' : 'none';
                });
            }

            // Busca no servidor (busca em todas as páginas)
            function searchOnServer() {
                const searchTerm = searchInput.value.trim();
                const url = new URL(window.location);
                
                if (searchTerm) {
                    url.searchParams.set('search', searchTerm);
                } else {
                    url.searchParams.delete('search');
                }
                
                // Redefinir para página 1 ao buscar
                url.searchParams.set('page', '1');
                
                window.location.href = url.toString();
            }

            // Filtro em tempo real local (rápido)
            searchInput.addEventListener('input', function() {
                // Filtro local imediato
                filterTableLocal();
                
                // Limpar timeout anterior
                clearTimeout(searchTimeout);
                clearTimeout(serverSearchTimeout);
                
                // Após 1 segundo sem digitar, buscar no servidor
                serverSearchTimeout = setTimeout(function() {
                    const searchTerm = searchInput.value.trim();
                    const currentSearch = new URL(window.location).searchParams.get('search');
                    
                    // Só buscar no servidor se o termo mudou
                    if (searchTerm !== currentSearch) {
                        searchOnServer();
                    }
                }, 1000);
            });

            // Buscar no servidor ao pressionar Enter
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(serverSearchTimeout);
                    searchOnServer();
                }
            });

            // Filtrar na inicialização se houver termo de busca
            if (searchInput.value) {
                filterTableLocal();
            }
        });
    </script>
</x-app-layout>

