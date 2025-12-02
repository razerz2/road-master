<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-900 dark:text-gray-100 leading-tight flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                {{ __('Detalhes da Notificação') }}
            </h2>
            <a href="{{ route('notifications.index') }}" class="inline-flex items-center px-4 py-2.5 bg-gray-600 text-white font-semibold rounded-lg shadow-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="p-6">
                    <div class="flex items-start space-x-4 mb-6">
                        <!-- Ícone do tipo -->
                        <div class="flex-shrink-0">
                            @if($notification->type === 'info')
                                <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @elseif($notification->type === 'success')
                                <div class="w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @elseif($notification->type === 'warning')
                                <div class="w-16 h-16 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                            @elseif($notification->type === 'error')
                                <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Conteúdo -->
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $notification->title }}
                                </h3>
                                @if(!$notification->read)
                                    <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-500 text-white">
                                        Nova
                                    </span>
                                @endif
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                        {{ $notification->message }}
                                    </p>
                                </div>
                                
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 capitalize">
                                                {{ $notification->type }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Data de Criação</dt>
                                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $notification->created_at->format('d/m/Y H:i:s') }}
                                            </dd>
                                        </div>
                                        @if($notification->read && $notification->read_at)
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Data de Leitura</dt>
                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $notification->read_at->format('d/m/Y H:i:s') }}
                                                </dd>
                                            </div>
                                        @endif
                                        @if($notification->link)
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Link</dt>
                                                <dd class="mt-1">
                                                    <a href="{{ $notification->link }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                                        {{ $notification->link }}
                                                    </a>
                                                </dd>
                                            </div>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ações -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            @if($notification->link)
                                <a href="{{ $notification->link }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                                    Acessar Link
                                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                        
                        <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta notificação?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-300 font-semibold rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Excluir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

