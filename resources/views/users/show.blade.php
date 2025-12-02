<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detalhes do Usuário') }}
            </h2>
            <div class="flex gap-2">
                @can('update', $user)
                <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                </a>
                @endcan
                <a href="{{ route('users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
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
                    <div class="flex items-start space-x-6">
                        <!-- Foto de Perfil -->
                        <div class="flex-shrink-0">
                            @php
                                $avatarUrl = $user->avatar ? route('storage.serve', ['path' => $user->avatar]) : null;
                            @endphp
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="w-32 h-32 rounded-full object-cover border-4 border-gray-300 dark:border-gray-600">
                            @else
                                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-4xl font-semibold border-4 border-gray-300 dark:border-gray-600">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <!-- Dados do Usuário -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                                {{ $user->name }}
                            </h3>
                            @if($user->name_full)
                                <p class="text-lg text-gray-600 dark:text-gray-400 mb-4">
                                    {{ $user->name_full }}
                                </p>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                                    <p class="text-base text-gray-900 dark:text-gray-100 font-medium">{{ $user->email }}</p>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Perfil</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' }}">
                                        {{ $user->role === 'admin' ? 'Admin' : 'Condutor' }}
                                    </span>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $user->active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                        {{ $user->active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Data de Cadastro</p>
                                    <p class="text-base text-gray-900 dark:text-gray-100 font-medium">
                                        {{ $user->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>

                                @if($user->updated_at && $user->updated_at->ne($user->created_at))
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Última Atualização</p>
                                    <p class="text-base text-gray-900 dark:text-gray-100 font-medium">
                                        {{ $user->updated_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Módulos de Acesso -->
            @if($user->role !== 'admin')
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Módulos de Acesso
                    </h3>
                    
                    @if($userPermissions->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($modules as $module)
                                @php
                                    $permission = $userPermissions->get($module->id);
                                    $hasAccess = $permission !== null;
                                @endphp
                                @if($hasAccess)
                                    <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
                                        <div class="flex items-center mb-3">
                                            <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $module->name }}</h4>
                                        </div>
                                        <div class="ml-7 grid grid-cols-2 gap-2">
                                            @if($permission->can_view)
                                                <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                                    <svg class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    Visualizar
                                                </div>
                                            @endif
                                            @if($permission->can_create)
                                                <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                                    <svg class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                    Criar
                                                </div>
                                            @endif
                                            @if($permission->can_edit)
                                                <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                                    <svg class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    Editar
                                                </div>
                                            @endif
                                            @if($permission->can_delete)
                                                <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                                                    <svg class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Excluir
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum módulo de acesso configurado.</p>
                    @endif
                </div>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Módulos de Acesso
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Usuário administrador tem acesso completo a todos os módulos do sistema.
                    </p>
                </div>
            </div>
            @endif

            <!-- Veículos sob Responsabilidade -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Veículos sob Responsabilidade
                    </h3>
                    
                    @if($userVehicles->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($userVehicles as $vehicle)
                                <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $vehicle->name }}</h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $vehicle->plate }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $vehicle->active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ $vehicle->active ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum veículo atribuído a este usuário.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

