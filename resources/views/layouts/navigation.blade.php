<nav x-data="{ open: false }" class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-50 w-full">
    <!-- Primary Navigation Menu -->
    <div class="w-full flex justify-between items-center h-16 pr-4 sm:pr-6 lg:pr-8">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center navigation-logo-wrapper">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 group">
                        @php
                            $logoPath = \App\Models\SystemSetting::get('system_logo');
                            $logoUrl = $logoPath ? route('storage.serve', ['path' => $logoPath]) : null;
                            $appName = \App\Models\SystemSetting::get('app_name') ?? config('app.name', 'Road Master');
                        @endphp
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="block h-9 w-auto object-contain">
                        @else
                            <x-application-logo class="block h-9 w-auto fill-current text-indigo-600 dark:text-indigo-400 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 transition-colors" />
                            <span class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">
                                {{ $appName }}
                            </span>
                        @endif
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 lg:space-x-2 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    
                    <!-- Frota Dropdown -->
                    @php
                        $hasFleetAccess = Gate::allows('viewAny', App\Models\Vehicle::class) ||
                                         Gate::allows('viewAny', App\Models\Fueling::class) ||
                                         Gate::allows('viewAny', App\Models\Maintenance::class) ||
                                         Gate::allows('viewAny', App\Models\ReviewNotification::class) ||
                                         Gate::allows('viewAny', App\Models\VehicleMandatoryEvent::class);
                    @endphp
                    @if($hasFleetAccess)
                    <x-nav-dropdown :active="request()->routeIs('vehicles.*') || request()->routeIs('fuelings.*') || request()->routeIs('maintenances.*') || request()->routeIs('review-notifications.*') || request()->routeIs('mandatory-events.*')">
                        <x-slot name="trigger">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            {{ __('Frota') }}
                        </x-slot>
                        <x-slot name="content">
                            @can('viewAny', App\Models\Vehicle::class)
                            <x-nav-dropdown-link :href="route('vehicles.index')" :active="request()->routeIs('vehicles.*')">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                    {{ __('Veículos') }}
                                </div>
                            </x-nav-dropdown-link>
                            @endcan
                            @can('viewAny', App\Models\Fueling::class)
                            <x-nav-dropdown-link :href="route('fuelings.index')" :active="request()->routeIs('fuelings.*')">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                    </svg>
                                    {{ __('Abastecimentos') }}
                                </div>
                            </x-nav-dropdown-link>
                            @endcan
                            @can('viewAny', App\Models\Maintenance::class)
                            <x-nav-dropdown-link :href="route('maintenances.index')" :active="request()->routeIs('maintenances.*')">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ __('Manutenções') }}
                                </div>
                            </x-nav-dropdown-link>
                            @endcan
                            @can('viewAny', App\Models\ReviewNotification::class)
                            <x-nav-dropdown-link :href="route('review-notifications.index')" :active="request()->routeIs('review-notifications.*')">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ __('Revisão') }}
                                </div>
                            </x-nav-dropdown-link>
                            @endcan
                            @can('viewAny', App\Models\VehicleMandatoryEvent::class)
                            <x-nav-dropdown-link :href="route('mandatory-events.index')" :active="request()->routeIs('mandatory-events.*')">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    {{ __('Obrigações Legais') }}
                                </div>
                            </x-nav-dropdown-link>
                            @endcan
                        </x-slot>
                    </x-nav-dropdown>
                    @endif

                    <!-- Operações Dropdown -->
                    @php
                        $hasOperationsAccess = Gate::allows('viewAny', App\Models\Trip::class) ||
                                             Gate::allows('viewAny', App\Models\Location::class);
                    @endphp
                    @if($hasOperationsAccess)
                    <x-nav-dropdown :active="request()->routeIs('trips.*') || request()->routeIs('locations.*')">
                        <x-slot name="trigger">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                            {{ __('Operações') }}
                        </x-slot>
                        <x-slot name="content">
                            @can('viewAny', App\Models\Trip::class)
                            <x-nav-dropdown-link :href="route('trips.index')" :active="request()->routeIs('trips.*')">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                    {{ __('Percursos') }}
                                </div>
                            </x-nav-dropdown-link>
                            @endcan
                            @can('viewAny', App\Models\Location::class)
                            <x-nav-dropdown-link :href="route('locations.index')" :active="request()->routeIs('locations.*')">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ __('Locais') }}
                                </div>
                            </x-nav-dropdown-link>
                            @endcan
                        </x-slot>
                    </x-nav-dropdown>
                    @endif

                    @if(Auth::user()->role === 'admin' || Auth::user()->hasPermission('reports', 'view'))
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        {{ __('Relatórios') }}
                    </x-nav-link>
                    @endif
                    @can('viewAny', App\Models\User::class)
                    <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        {{ __('Usuários') }}
                    </x-nav-link>
                    @endcan
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center sm:hidden ms-4">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Right Side Actions (Notifications + Profile) -->
            <div class="hidden sm:flex sm:items-center sm:justify-end">
                <!-- Notifications Dropdown (Desktop) -->
                <div class="flex items-center" x-data="{ 
                    unreadCount: {{ Auth::check() ? Auth::user()->notifications()->unread()->count() : 0 }},
                    notifications: [],
                    pollingInterval: null,
                    lastCount: {{ Auth::check() ? Auth::user()->notifications()->unread()->count() : 0 }},
                    loadCount() {
                        @auth
                        return fetch('{{ route('notifications.api.unread-count') }}')
                            .then(response => response.json())
                            .then(data => {
                                const newCount = data.count || 0;
                                const hadNewNotifications = newCount > this.lastCount;
                                
                                if (hadNewNotifications) {
                                    this.lastCount = newCount;
                                }
                                
                                this.unreadCount = newCount;
                                return hadNewNotifications;
                            })
                            .catch(error => {
                                console.error('Erro ao carregar contador:', error);
                                return false;
                            });
                        @endauth
                    },
                    loadNotificationsList() {
                        @auth
                        return fetch('{{ route('notifications.api.latest') }}')
                            .then(response => response.json())
                            .then(data => {
                                this.notifications = data;
                            })
                            .catch(error => {
                                console.error('Erro ao carregar notificações:', error);
                            });
                        @endauth
                    },
                    loadNotifications(forceLoadList = false) {
                        @auth
                        this.loadCount().then(hadNew => {
                            // Carregar lista se houver novas notificações ou se forçado
                            if (hadNew || forceLoadList) {
                                this.loadNotificationsList();
                            }
                        });
                        @endauth
                    },
                    startPolling() {
                        @auth
                        // Carregar notificações imediatamente
                        this.loadNotifications(true);
                        
                        // Configurar polling a cada 10 segundos
                        this.pollingInterval = setInterval(() => {
                            this.loadNotifications();
                        }, 10000);
                        @endauth
                    },
                    stopPolling() {
                        if (this.pollingInterval) {
                            clearInterval(this.pollingInterval);
                            this.pollingInterval = null;
                        }
                    }
                }" 
                x-init="startPolling()" 
                @visibilitychange.window="document.hidden ? stopPolling() : startPolling()">
                    <div class="relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                        <div @click="dropdownOpen = !dropdownOpen; if (dropdownOpen) loadNotifications(true);" class="mr-4">
                            <button class="relative inline-flex items-center px-3 py-2 border border-gray-200 dark:border-gray-700 text-sm leading-4 font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm hover:shadow">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span x-show="unreadCount > 0" class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-gray-800"></span>
                                <span x-show="unreadCount > 0" class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                            </button>
                        </div>

                        <div x-show="dropdownOpen"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-50 mt-2 w-80 rounded-md shadow-lg ltr:origin-top-right rtl:origin-top-left end-0"
                            style="display: none;"
                            @click="dropdownOpen = false">
                            <div class="rounded-md ring-1 ring-black ring-opacity-5 py-1 bg-white dark:bg-gray-700">
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notificações</h3>
                                    <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                        Ver todas
                                    </a>
                                </div>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                <template x-if="notifications.length === 0">
                                    <div class="px-4 py-8 text-center">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma notificação</p>
                                    </div>
                                </template>
                                <template x-for="notification in notifications" :key="notification.id">
                                    <a :href="'{{ route('notifications.index') }}/' + notification.id" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div x-show="notification.type === 'info'" class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div x-show="notification.type === 'success'" class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div x-show="notification.type === 'warning'" class="w-8 h-8 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                </div>
                                                <div x-show="notification.type === 'error'" class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="notification.title"></p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2" x-text="notification.message"></p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" x-text="new Date(notification.created_at).toLocaleString('pt-BR')"></p>
                                            </div>
                                            <div x-show="!notification.read" class="flex-shrink-0">
                                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                                <a href="{{ route('notifications.index') }}" class="block text-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                                    Ver todas as notificações
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Dropdown (Desktop) -->
                <div class="flex items-center" x-data="{ darkMode: document.documentElement.classList.contains('dark') }">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm leading-4 font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm hover:shadow">
                            <div class="flex items-center space-x-2">
                                @php
                                    $userAvatar = Auth::user()->avatar ? route('storage.serve', ['path' => Auth::user()->avatar]) : null;
                                @endphp
                                @if($userAvatar)
                                    <img src="{{ $userAvatar }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover border-2 border-gray-300 dark:border-gray-600">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-semibold">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="font-medium">{{ Auth::user()->name }}</span>
                            </div>
                            <div class="ms-2">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Toggle Dark Mode -->
                        <button 
                            type="button"
                            @click="
                                darkMode = !darkMode;
                                if (darkMode) {
                                    document.documentElement.classList.add('dark');
                                    localStorage.setItem('theme', 'dark');
                                } else {
                                    document.documentElement.classList.remove('dark');
                                    localStorage.setItem('theme', 'light');
                                }
                            "
                            class="w-full text-left px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition duration-150 ease-in-out"
                        >
                            <div class="flex items-center">
                                <svg x-show="!darkMode" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                </svg>
                                <svg x-show="darkMode" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span x-text="darkMode ? 'Modo Claro' : 'Modo Escuro'"></span>
                            </div>
                        </button>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @can('viewAny', App\Models\User::class)
                        <x-dropdown-link :href="route('settings.index')">
                            {{ __('Configurações') }}
                        </x-dropdown-link>
                        @endcan

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                </div>
            </div>
        </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden overflow-y-auto max-h-screen">
        <div class="pt-2 pb-3 space-y-1 px-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            
            <!-- Frota (Mobile) -->
            @php
                $hasFleetAccessMobile = Gate::allows('viewAny', App\Models\Vehicle::class) ||
                                       Gate::allows('viewAny', App\Models\Fueling::class) ||
                                       Gate::allows('viewAny', App\Models\Maintenance::class) ||
                                       Gate::allows('viewAny', App\Models\ReviewNotification::class) ||
                                       Gate::allows('viewAny', App\Models\VehicleMandatoryEvent::class);
            @endphp
            @if($hasFleetAccessMobile)
            <div class="px-3 py-2">
                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                    {{ __('Frota') }}
                </div>
                <div class="ml-2 space-y-1">
                    @can('viewAny', App\Models\Vehicle::class)
                    <x-responsive-nav-link :href="route('vehicles.index')" :active="request()->routeIs('vehicles.*')">
                        {{ __('Veículos') }}
                    </x-responsive-nav-link>
                    @endcan
                    @can('viewAny', App\Models\Fueling::class)
                    <x-responsive-nav-link :href="route('fuelings.index')" :active="request()->routeIs('fuelings.*')">
                        {{ __('Abastecimentos') }}
                    </x-responsive-nav-link>
                    @endcan
                    @can('viewAny', App\Models\Maintenance::class)
                    <x-responsive-nav-link :href="route('maintenances.index')" :active="request()->routeIs('maintenances.*')">
                        {{ __('Manutenções') }}
                    </x-responsive-nav-link>
                    @endcan
                    @can('viewAny', App\Models\ReviewNotification::class)
                    <x-responsive-nav-link :href="route('review-notifications.index')" :active="request()->routeIs('review-notifications.*')">
                        {{ __('Notificações de Revisão') }}
                    </x-responsive-nav-link>
                    @endcan
                    @can('viewAny', App\Models\VehicleMandatoryEvent::class)
                    <x-responsive-nav-link :href="route('mandatory-events.index')" :active="request()->routeIs('mandatory-events.*')">
                        {{ __('Obrigações Legais') }}
                    </x-responsive-nav-link>
                    @endcan
                </div>
            </div>
            @endif

            <!-- Operações (Mobile) -->
            @php
                $hasOperationsAccessMobile = Gate::allows('viewAny', App\Models\Trip::class) ||
                                           Gate::allows('viewAny', App\Models\Location::class);
            @endphp
            @if($hasOperationsAccessMobile)
            <div class="px-3 py-2">
                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                    {{ __('Operações') }}
                </div>
                <div class="ml-2 space-y-1">
                    @can('viewAny', App\Models\Trip::class)
                    <x-responsive-nav-link :href="route('trips.index')" :active="request()->routeIs('trips.*')">
                        {{ __('Percursos') }}
                    </x-responsive-nav-link>
                    @endcan
                    @can('viewAny', App\Models\Location::class)
                    <x-responsive-nav-link :href="route('locations.index')" :active="request()->routeIs('locations.*')">
                        {{ __('Locais') }}
                    </x-responsive-nav-link>
                    @endcan
                </div>
            </div>
            @endif

            @if(Auth::user()->role === 'admin' || Auth::user()->hasPermission('reports', 'view'))
            <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                {{ __('Relatórios') }}
            </x-responsive-nav-link>
            @endif
            @can('viewAny', App\Models\User::class)
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                {{ __('Usuários') }}
            </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4 pb-3 flex items-center space-x-3">
                @php
                    $userAvatar = Auth::user()->avatar ? route('storage.serve', ['path' => Auth::user()->avatar]) : null;
                @endphp
                @if($userAvatar)
                    <img src="{{ $userAvatar }}" alt="{{ Auth::user()->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-gray-300 dark:border-gray-600">
                @else
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-semibold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200 truncate">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500 truncate">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-1 space-y-1 px-2" x-data="{ darkMode: document.documentElement.classList.contains('dark') }">
                <!-- Toggle Dark Mode (Mobile) -->
                <button 
                    type="button"
                    @click="
                        darkMode = !darkMode;
                        if (darkMode) {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('theme', 'dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                            localStorage.setItem('theme', 'light');
                        }
                    "
                    class="w-full text-left block px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition duration-150 ease-in-out"
                >
                    <div class="flex items-center">
                        <svg x-show="!darkMode" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg x-show="darkMode" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span x-text="darkMode ? 'Modo Claro' : 'Modo Escuro'"></span>
                    </div>
                </button>

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                @can('viewAny', App\Models\User::class)
                <x-responsive-nav-link :href="route('settings.index')">
                    {{ __('Configurações') }}
                </x-responsive-nav-link>
                @endcan

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Sair') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
