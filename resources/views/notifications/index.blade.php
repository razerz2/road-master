<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-900 dark:text-gray-100 leading-tight flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                {{ __('Notificações') }}
                @if($unreadCount > 0)
                    <span class="ml-3 px-2.5 py-0.5 text-xs font-semibold rounded-full bg-red-500 text-white">
                        {{ $unreadCount }} não lidas
                    </span>
                @endif
            </h2>
            @if($unreadCount > 0)
            <button onclick="markAllAsRead()" class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Marcar todas como lidas
            </button>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border-l-4 border-emerald-500 rounded-lg shadow-sm">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-emerald-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Filtros -->
            <div class="card">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtros
                    </h3>
                    <form method="GET" action="{{ route('notifications.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label>Tipo</x-input-label>
                            <select name="type" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 shadow-sm">
                                <option value="all" {{ request('type') == 'all' || !request('type') ? 'selected' : '' }}>Todos</option>
                                <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Informação</option>
                                <option value="success" {{ request('type') == 'success' ? 'selected' : '' }}>Sucesso</option>
                                <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Aviso</option>
                                <option value="error" {{ request('type') == 'error' ? 'selected' : '' }}>Erro</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label>Status</x-input-label>
                            <select name="status" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 shadow-sm">
                                <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Todas</option>
                                <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Não lidas</option>
                                <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Lidas</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <x-primary-button type="submit" class="w-full">Filtrar</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Notificações -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lista de Notificações</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($notifications as $notification)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ !$notification->read ? 'bg-blue-50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800' : '' }}">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-4 flex-1">
                                        <!-- Ícone do tipo -->
                                        <div class="flex-shrink-0">
                                            @if($notification->type === 'info')
                                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                            @elseif($notification->type === 'success')
                                                <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                            @elseif($notification->type === 'warning')
                                                <div class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                </div>
                                            @elseif($notification->type === 'error')
                                                <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Conteúdo -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $notification->title }}
                                                </h4>
                                                @if(!$notification->read)
                                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-500 text-white">
                                                        Nova
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                {{ $notification->message }}
                                            </p>
                                            <div class="flex items-center space-x-4 text-xs text-gray-500 dark:text-gray-400">
                                                <span>{{ $notification->created_at->format('d/m/Y H:i') }}</span>
                                                @if($notification->read && $notification->read_at)
                                                    <span>Lida em {{ $notification->read_at->format('d/m/Y H:i') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Ações -->
                                    <div class="flex items-center space-x-2 ml-4">
                                        @if($notification->link)
                                            <a href="{{ $notification->link }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                                Ver
                                            </a>
                                        @else
                                            <a href="{{ route('notifications.show', $notification) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                                Ver
                                            </a>
                                        @endif
                                        <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline" onsubmit="event.preventDefault(); if (typeof handleDelete === 'function') { handleDelete(this, 'Tem certeza que deseja excluir esta notificação?'); } else { if (confirm('Tem certeza que deseja excluir esta notificação?')) { this.submit(); } }">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-300 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nenhuma notificação encontrada</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Você não possui notificações no momento</p>
                            </div>
                        @endforelse
                    </div>
                    
                    @if($notifications->hasPages())
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

<script>
async function markAllAsRead() {
    let confirmed = false;
    if (window.showConfirm) {
        confirmed = await window.showConfirm('Tem certeza que deseja marcar todas as notificações como lidas?', 'Marcar como Lidas');
    } else {
        confirmed = confirm('Tem certeza que deseja marcar todas as notificações como lidas?');
    }
    if (confirmed) {
        fetch('{{ route("notifications.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Erro:', error);
        });
    }
}
</script>
</x-app-layout>

