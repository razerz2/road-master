<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Configurações do Sistema') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 dark:bg-green-800 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 dark:bg-red-800 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" x-data="{ activeTab: '{{ request()->get('activeTab', 'general') }}' }">
                <!-- Tabs -->
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
                        <button 
                            type="button"
                            @click="activeTab = 'general'"
                            :class="activeTab === 'general' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Geral
                        </button>
                        <button 
                            type="button"
                            @click="activeTab = 'dashboard'"
                            :class="activeTab === 'dashboard' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Dashboard
                        </button>
                        <button 
                            type="button"
                            @click="activeTab = 'appearance'"
                            :class="activeTab === 'appearance' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Aparência
                        </button>
                        <button 
                            type="button"
                            @click="activeTab = 'catalogs'"
                            :class="activeTab === 'catalogs' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Catálogos
                        </button>
                        <button 
                            type="button"
                            @click="activeTab = 'import'"
                            :class="activeTab === 'import' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Importação
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Tab: Configurações do Dashboard -->
                    <div x-show="activeTab === 'dashboard'" x-transition>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Preferências do Dashboard</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Configure os filtros padrão que serão aplicados automaticamente ao acessar o dashboard.
                        </p>
                        
                        <form method="POST" action="{{ route('settings.updateDashboardPreferences') }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <x-input-label for="dashboard_start_date" :value="__('Data Inicial Padrão')" />
                                        <x-text-input 
                                            id="dashboard_start_date" 
                                            class="block mt-1 w-full" 
                                            type="date" 
                                            name="dashboard_start_date" 
                                            :value="old('dashboard_start_date', $dashboardPreferences['start_date'])" 
                                            required 
                                        />
                                        <x-input-error :messages="$errors->get('dashboard_start_date')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Data inicial que será usada por padrão ao abrir o dashboard.
                                        </p>
                                    </div>

                                    <div>
                                        <x-input-label for="dashboard_end_date" :value="__('Data Final Padrão')" />
                                        <x-text-input 
                                            id="dashboard_end_date" 
                                            class="block mt-1 w-full" 
                                            type="date" 
                                            name="dashboard_end_date" 
                                            :value="old('dashboard_end_date', $dashboardPreferences['end_date'])" 
                                            required 
                                        />
                                        <x-input-error :messages="$errors->get('dashboard_end_date')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Data final que será usada por padrão ao abrir o dashboard.
                                        </p>
                                    </div>

                                    <div>
                                        <x-input-label for="dashboard_vehicle_id" :value="__('Veículo Padrão')" />
                                        <select id="dashboard_vehicle_id" name="dashboard_vehicle_id" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm">
                                            <option value="">Todos os veículos</option>
                                            @foreach($vehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}" {{ old('dashboard_vehicle_id', $dashboardPreferences['vehicle_id']) == $vehicle->id ? 'selected' : '' }}>
                                                    {{ $vehicle->name }} - {{ $vehicle->plate }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('dashboard_vehicle_id')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Veículo que será selecionado por padrão ao abrir o dashboard. Deixe em branco para mostrar todos.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <x-primary-button>
                                        {{ __('Salvar Preferências') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        @method('PUT')

                        <!-- Tab: Configurações Gerais -->
                        <div x-show="activeTab === 'general'" x-transition>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Configurações Gerais do Sistema</h3>
                            
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-input-label for="app_name" :value="__('Nome da Aplicação')" />
                                        <x-text-input 
                                            id="app_name" 
                                            class="block mt-1 w-full" 
                                            type="text" 
                                            name="app_name" 
                                            :value="old('app_name', $settings['general']['app_name'] ?? config('app.name', 'SCKV'))" 
                                            required 
                                        />
                                        <x-input-error :messages="$errors->get('app_name')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Nome exibido no cabeçalho do sistema.
                                        </p>
                                    </div>

                                    <div>
                                        <x-input-label for="timezone" :value="__('Fuso Horário')" />
                                        <select id="timezone" name="timezone" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                                            <option value="America/Sao_Paulo" {{ old('timezone', $settings['general']['timezone'] ?? 'America/Sao_Paulo') == 'America/Sao_Paulo' ? 'selected' : '' }}>America/Sao_Paulo (Brasil)</option>
                                            <option value="America/Manaus" {{ old('timezone', $settings['general']['timezone'] ?? '') == 'America/Manaus' ? 'selected' : '' }}>America/Manaus</option>
                                            <option value="America/Fortaleza" {{ old('timezone', $settings['general']['timezone'] ?? '') == 'America/Fortaleza' ? 'selected' : '' }}>America/Fortaleza</option>
                                            <option value="UTC" {{ old('timezone', $settings['general']['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="date_format" :value="__('Formato de Data')" />
                                        <select id="date_format" name="date_format" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                                            <option value="d/m/Y" {{ old('date_format', $settings['general']['date_format'] ?? 'd/m/Y') == 'd/m/Y' ? 'selected' : '' }}>dd/mm/aaaa (31/12/2025)</option>
                                            <option value="Y-m-d" {{ old('date_format', $settings['general']['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>aaaa-mm-dd (2025-12-31)</option>
                                            <option value="m/d/Y" {{ old('date_format', $settings['general']['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }}>mm/dd/aaaa (12/31/2025)</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('date_format')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="time_format" :value="__('Formato de Hora')" />
                                        <select id="time_format" name="time_format" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                                            <option value="H:i" {{ old('time_format', $settings['general']['time_format'] ?? 'H:i') == 'H:i' ? 'selected' : '' }}>24 horas (14:30)</option>
                                            <option value="h:i A" {{ old('time_format', $settings['general']['time_format'] ?? '') == 'h:i A' ? 'selected' : '' }}>12 horas (02:30 PM)</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('time_format')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="currency" :value="__('Moeda')" />
                                        <select id="currency" name="currency" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                                            <option value="BRL" {{ old('currency', $settings['general']['currency'] ?? 'BRL') == 'BRL' ? 'selected' : '' }}>BRL - Real Brasileiro</option>
                                            <option value="USD" {{ old('currency', $settings['general']['currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD - Dólar Americano</option>
                                            <option value="EUR" {{ old('currency', $settings['general']['currency'] ?? '') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('currency')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="currency_symbol" :value="__('Símbolo da Moeda')" />
                                        <x-text-input 
                                            id="currency_symbol" 
                                            class="block mt-1 w-full" 
                                            type="text" 
                                            name="currency_symbol" 
                                            :value="old('currency_symbol', $settings['general']['currency_symbol'] ?? 'R$')" 
                                            required 
                                            maxlength="10"
                                        />
                                        <x-input-error :messages="$errors->get('currency_symbol')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Símbolo usado para exibir valores monetários.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botão de salvar (visível apenas na aba general) -->
                        <div x-show="activeTab === 'general'" class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <x-primary-button type="submit">
                                {{ __('Salvar Configurações') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <!-- Tab: Importação (fora do formulário principal) -->
                    <div x-show="activeTab === 'import'" x-transition>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Importação de Planilhas de KM</h3>
                        
                        @if(session('success'))
                            <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="mb-4 p-4 bg-red-100 dark:bg-red-800 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 rounded">
                                <ul class="list-disc list-inside">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('import.process') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="file" :value="__('Selecione o arquivo')" />
                                    <input type="file" name="file" id="file" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" accept=".xlsx,.xls" required>
                                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Formatos aceitos: .xlsx, .xls</p>
                                </div>

                                <div>
                                    <x-input-label for="year" :value="__('Ano da planilha')" />
                                    <x-text-input id="year" class="block mt-1 w-full" type="number" name="year" :value="old('year', date('Y'))" min="2000" max="2100" required />
                                    <x-input-error :messages="$errors->get('year')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="vehicle_id" :value="__('Veículo para vincular os percursos')" />
                                    <select id="vehicle_id" name="vehicle_id" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                                        <option value="">Selecione...</option>
                                        @foreach($vehicles as $v)
                                            <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->name }} - {{ $v->plate }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <x-primary-button>
                                    {{ __('Importar Dados') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Aparência (formulário separado) -->
                    <div x-show="activeTab === 'appearance'" x-transition>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Configurações de Aparência</h3>
                        
                        <form action="{{ route('settings.updateAppearance') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                                <div class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Logo do Sistema -->
                                        <div>
                                            <x-input-label for="logo" :value="__('Logo do Sistema')" />
                                            <div class="mt-2">
                                                @php
                                                    $logoPath = \App\Models\SystemSetting::get('system_logo');
                                                    $logoUrl = $logoPath ? route('storage.serve', ['path' => $logoPath]) : null;
                                                @endphp
                                                @if($logoUrl)
                                                    <div class="mb-4">
                                                        <img src="{{ $logoUrl }}" alt="Logo atual" class="max-h-32 object-contain border border-gray-300 dark:border-gray-700 rounded p-2 bg-white dark:bg-gray-900">
                                                    </div>
                                                @else
                                                    <div class="mb-4 p-4 border border-gray-300 dark:border-gray-700 rounded bg-gray-50 dark:bg-gray-900">
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">Usando logo padrão do Laravel</p>
                                                    </div>
                                                @endif
                                                <input type="file" name="logo" id="logo" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900 dark:file:text-indigo-300" accept="image/png,image/jpeg,image/jpg,image/svg+xml">
                                                <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                    Formatos aceitos: PNG, JPG, JPEG, SVG. Tamanho recomendado: 200x50px.
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Favicon -->
                                        <div>
                                            <x-input-label for="favicon" :value="__('Favicon (Ícone da Guia)')" />
                                            <div class="mt-2">
                                                @php
                                                    $faviconPath = \App\Models\SystemSetting::get('system_favicon');
                                                    $faviconUrl = $faviconPath ? route('storage.serve', ['path' => $faviconPath]) : null;
                                                @endphp
                                                @if($faviconUrl)
                                                    <div class="mb-4">
                                                        <img src="{{ $faviconUrl }}" alt="Favicon atual" class="h-16 w-16 object-contain border border-gray-300 dark:border-gray-700 rounded p-2 bg-white dark:bg-gray-900">
                                                    </div>
                                                @else
                                                    <div class="mb-4 p-4 border border-gray-300 dark:border-gray-700 rounded bg-gray-50 dark:bg-gray-900">
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">Usando favicon padrão do Laravel</p>
                                                    </div>
                                                @endif
                                                <input type="file" name="favicon" id="favicon" class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900 dark:file:text-indigo-300" accept="image/png,image/x-icon,image/svg+xml,.ico">
                                                <x-input-error :messages="$errors->get('favicon')" class="mt-2" />
                                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                    Formatos aceitos: PNG, ICO, SVG. Tamanho recomendado: 32x32px ou 16x16px.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                                        <button type="button" onclick="resetAppearance()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Redefinir para Padrão
                                        </button>
                                        <x-primary-button>
                                            {{ __('Salvar Configurações') }}
                                        </x-primary-button>
                                    </div>
                                </div>
                            </form>
                            
                            <!-- Formulário de reset separado (oculto) -->
                            <form id="resetAppearanceForm" action="{{ route('settings.resetAppearance') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                    </div>

                    <!-- Tab: Catálogos -->
                    <div x-show="activeTab === 'catalogs'" x-transition>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Gerenciar Catálogos do Sistema</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Gerencie os catálogos de tipos e métodos utilizados no sistema. Todos os CRUDs estão disponíveis na mesma página.
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Tipos de Combustível -->
                            <a 
                                href="{{ route('fuel-types.index') }}"
                                class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tipos de Combustível</h4>
                                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Gerencie os tipos de combustível disponíveis para veículos.
                                </p>
                            </a>

                            <!-- Métodos de Pagamento -->
                            <a 
                                href="{{ route('payment-methods.index') }}"
                                class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Métodos de Pagamento</h4>
                                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Gerencie os métodos de pagamento para abastecimentos.
                                </p>
                            </a>

                            <!-- Tipos de Manutenção -->
                            <a 
                                href="{{ route('maintenance-types.index') }}"
                                class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tipos de Manutenção</h4>
                                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Gerencie os tipos de manutenção disponíveis.
                                </p>
                            </a>

                            <!-- Tipos de Local -->
                            <a 
                                href="{{ route('location-types.index') }}"
                                class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tipos de Local</h4>
                                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Gerencie os tipos de local disponíveis.
                                </p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function resetAppearance() {
            if (confirm('Tem certeza que deseja redefinir as logos para o padrão do Laravel?')) {
                document.getElementById('resetAppearanceForm').submit();
            }
        }
    </script>
</x-app-layout>
