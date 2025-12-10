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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg" x-data="{ activeTab: '{{ request()->get('activeTab', 'general') }}', importType: 'trips' }">
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
                            @click="activeTab = 'appearance'"
                            :class="activeTab === 'appearance' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Aparência
                        </button>
                        <button 
                            type="button"
                            @click="activeTab = 'email'"
                            :class="activeTab === 'email' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Email
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
                            @click="activeTab = 'profiles'"
                            :class="activeTab === 'profiles' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Perfis
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
                        <button 
                            type="button"
                            @click="activeTab = 'export'"
                            :class="activeTab === 'export' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Exportação
                        </button>
                        <button 
                            type="button"
                            @click="activeTab = 'notifications'"
                            :class="activeTab === 'notifications' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm"
                        >
                            Notificações
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
                                            :value="old('app_name', $settings['general']['app_name'] ?? config('app.name', 'Road Master'))" 
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
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Importação de Planilhas</h3>
                        
                        <!-- Abas internas para escolher tipo de importação -->
                        <div class="mb-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <nav class="flex -mb-px space-x-8" aria-label="Tabs">
                                <button 
                                    type="button"
                                    @click="importType = 'trips'"
                                    :class="importType === 'trips' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                    x-init="importType = importType || 'trips'"
                                >
                                    Importar Viagens (KM)
                                </button>
                                <button 
                                    type="button"
                                    @click="importType = 'locations'"
                                    :class="importType === 'locations' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                                >
                                    Importar Locais
                                </button>
                            </nav>
                        </div>

                        <!-- Conteúdo: Importação de Viagens -->
                        <div x-show="importType === 'trips'" x-transition>
                        <!-- Informações sobre a estrutura da planilha -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-6">
                            <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4">
                                📋 Estrutura da Planilha
                            </h4>
                            <div class="space-y-4 text-sm text-blue-800 dark:text-blue-200">
                                <div>
                                    <p class="font-medium mb-2">A planilha deve ter a seguinte estrutura na primeira linha (cabeçalho):</p>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full border border-blue-300 dark:border-blue-700 rounded-lg text-sm">
                                            <thead class="bg-blue-100 dark:bg-blue-800">
                                                <tr>
                                                    <th class="px-4 py-2 text-left border-b border-blue-300 dark:border-blue-700 font-semibold">Coluna</th>
                                                    <th class="px-4 py-2 text-left border-b border-blue-300 dark:border-blue-700 font-semibold">Cabeçalho</th>
                                                    <th class="px-4 py-2 text-left border-b border-blue-300 dark:border-blue-700 font-semibold">Descrição</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-gray-800">
                                                <tr>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">A</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700 font-medium">ITINERÁRIO</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">Rota separada por "-" (ex: "WPS - CD SEDE - RES. WILIAM")</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">B</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700 font-medium">DATA</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">Data da viagem (DD/MM/YYYY ou formato Excel)</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">C</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700 font-medium">HORÁRIO SAÍDA</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">Horário de saída (HH:MM ou formato Excel)</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">D</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700 font-medium">KM SAÍDA</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">Odômetro na saída (número)</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">E</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700 font-medium">HORÁRIO CHEGADA</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">Horário de chegada (HH:MM ou formato Excel)</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">F</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700 font-medium">KM CHEGADA</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">Odômetro na chegada (número)</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">G</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700 font-medium">KM RODADOS</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">Não utilizado (calculado automaticamente)</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">H</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700 font-medium">Tipo/Qtde</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">Tipo e quantidade (ex: "G-22,20" onde G=Gasolina, E=Etanol, D=Diesel)</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">I</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700 font-medium">Valor</td>
                                                    <td class="px-4 py-2 border-b border-blue-200 dark:border-blue-700">Valor do abastecimento (ex: "150,00" - use vírgula como separador decimal)</td>
                                                </tr>
                                                <tr>
                                                    <td class="px-4 py-2">J</td>
                                                    <td class="px-4 py-2 font-medium">CONDUTOR</td>
                                                    <td class="px-4 py-2">Nome do motorista (deve estar cadastrado no sistema)</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-blue-200 dark:border-blue-700">
                                    <p class="font-medium mb-2">📌 Observações Importantes:</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>A primeira linha deve ser o cabeçalho exatamente como mostrado acima</li>
                                        <li>Os dados começam na linha 2</li>
                                        <li>A linha com "TOTAL KM RODADOS" encerra a leitura automaticamente</li>
                                        <li>Campos obrigatórios: <strong>ITINERÁRIO</strong>, <strong>DATA</strong> e <strong>CONDUTOR</strong></li>
                                        <li>Se o primeiro e último local do itinerário forem iguais, será considerado retorno ao local de partida</li>
                                        <li>Locais com acentos diferentes serão considerados o mesmo (ex: "Júlio" = "Julio")</li>
                                        <li>KM Chegada deve ser maior ou igual a KM Saída</li>
                                    </ul>
                                </div>
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                    <p class="font-medium text-yellow-900 dark:text-yellow-100 mb-2">⚠️ Exemplo de Itinerário:</p>
                                    <p class="text-yellow-800 dark:text-yellow-200">
                                        <strong>Formato:</strong> "Origem - Parada 1 - Parada 2 - Destino"<br>
                                        <strong>Exemplo:</strong> "WPS - CD SEDE - RES. WILIAM - WPS"<br>
                                        <em>Nota: Se origem e destino forem iguais, o sistema considera o último local diferente como destino final e marca como retorno.</em>
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 p-4 bg-red-100 dark:bg-red-800 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 rounded">
                                {{ session('error') }}
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

                        <!-- Conteúdo: Importação de Locais -->
                        <div x-show="importType === 'locations'" x-transition>
                        <!-- Informações sobre a estrutura da planilha de locais -->
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 mb-6">
                            <h4 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-4">
                                📋 Importação de Locais
                            </h4>
                            <div class="space-y-4 text-sm text-green-800 dark:text-green-200">
                                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-green-200 dark:border-green-700">
                                    <p class="font-medium mb-2">ℹ️ Como Funciona:</p>
                                    <p class="mb-3">
                                        A importação de locais utiliza a <strong>mesma planilha de importação de KM</strong>, mas processa apenas a coluna <strong>ITINERÁRIO</strong> (coluna A).
                                    </p>
                                    <p class="mb-3">
                                        O sistema extrai todos os locais do campo ITINERÁRIO, separando-os pelo caractere <strong>"-"</strong> (hífen).
                                    </p>
                                    <p class="font-medium mb-2">📝 Exemplo:</p>
                                    <p class="mb-2">
                                        Se o ITINERÁRIO for: <strong>"Wps - Ag. Bandeirantes - Wps - Cd Sede - Restaurante - Cd Sede - Wps"</strong>
                                    </p>
                                    <p class="mb-3">
                                        O sistema criará/buscará os seguintes locais:
                                    </p>
                                    <ul class="list-disc list-inside ml-4 space-y-1">
                                        <li>Wps</li>
                                        <li>Ag. Bandeirantes</li>
                                        <li>Cd Sede</li>
                                        <li>Restaurante</li>
                                    </ul>
                                </div>
                                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-green-200 dark:border-green-700">
                                    <p class="font-medium mb-2">📌 Observações Importantes:</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>Use a <strong>mesma planilha</strong> da importação de viagens (KM)</li>
                                        <li>Apenas a coluna <strong>ITINERÁRIO</strong> (coluna A) será processada</li>
                                        <li>Os locais são separados pelo caractere <strong>"-"</strong> (hífen)</li>
                                        <li>Locais duplicados no mesmo itinerário são contados apenas uma vez</li>
                                        <li>Locais com nomes similares (ignorando acentos) serão considerados o mesmo local</li>
                                        <li>O sistema normaliza os nomes automaticamente (remove espaços duplos, padroniza capitalização)</li>
                                        <li>A linha com "TOTAL KM RODADOS" encerra a leitura automaticamente</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 p-4 bg-red-100 dark:bg-red-800 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 rounded">
                                {{ session('error') }}
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

                        <!-- Formulário de Importação de Locais -->
                        <form action="{{ route('import.locations') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <x-input-label for="file_locations" :value="__('Selecione o arquivo')" />
                                    <input type="file" name="file" id="file_locations" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" accept=".xlsx,.xls" required>
                                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Formatos aceitos: .xlsx, .xls</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <x-primary-button>
                                    {{ __('Importar Locais') }}
                                </x-primary-button>
                            </div>
                        </form>
                        </div>
                    </div>

                    <!-- Tab: Exportação -->
                    <div x-show="activeTab === 'export'" x-transition>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Exportação de Planilhas de KM</h3>
                        
                        @if(session('success'))
                            <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 rounded">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="mb-4 p-4 bg-red-100 dark:bg-red-800 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 rounded">
                                {{ session('error') }}
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

                        <!-- Informações sobre a exportação -->
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 mb-6">
                            <h4 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-3">
                                📤 Exportar Dados
                            </h4>
                            <div class="text-sm text-green-800 dark:text-green-200 space-y-2">
                                <p>
                                    Exporte os percursos cadastrados no sistema em formato Excel, seguindo exatamente a mesma estrutura da planilha de importação.
                                </p>
                                <p class="font-medium">
                                    A planilha exportada pode ser reimportada no sistema mantendo total compatibilidade.
                                </p>
                            </div>
                        </div>

                        <!-- Formulário de Exportação -->
                        <form action="{{ route('import.export') }}" method="GET">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="export_year" :value="__('Ano da planilha')" />
                                    <x-text-input id="export_year" class="block mt-1 w-full" type="number" name="year" :value="old('export_year', date('Y'))" min="2000" max="2100" required />
                                    <x-input-error :messages="$errors->get('year')" class="mt-2" />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ano dos percursos que deseja exportar</p>
                                </div>

                                <div>
                                    <x-input-label for="export_vehicle_id" :value="__('Veículo')" />
                                    <select id="export_vehicle_id" name="vehicle_id" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm" required>
                                        <option value="">Selecione...</option>
                                        @foreach($vehicles as $v)
                                            <option value="{{ $v->id }}" {{ old('export_vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->name }} - {{ $v->plate }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Veículo cujos percursos serão exportados</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <x-primary-button type="submit" class="bg-green-600 hover:bg-green-700">
                                    {{ __('Exportar Planilha') }}
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

                            <!-- Postos de Combustível -->
                            <a 
                                href="{{ route('gas-stations.index') }}"
                                class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Postos de Combustível</h4>
                                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Gerencie os postos de combustível cadastrados.
                                </p>
                            </a>
                        </div>
                    </div>

                    <!-- Tab: Perfis -->
                    <div x-show="activeTab === 'profiles'" x-transition>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Configurações de Perfis</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Configure os módulos padrão que serão pré-selecionados ao criar um novo usuário com perfil de condutor.
                        </p>
                        
                        <form method="POST" action="{{ route('settings.updateDriverDefaultModules') }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($modules as $module)
                                        <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                            <div class="flex items-center mb-3">
                                                <input 
                                                    type="checkbox" 
                                                    name="modules[{{ $module->id }}][enabled]" 
                                                    value="1" 
                                                    id="default_module_{{ $module->id }}"
                                                    class="default-module-checkbox rounded border-gray-300"
                                                    {{ in_array($module->id, $defaultDriverModules ?? []) ? 'checked' : '' }}
                                                >
                                                <label for="default_module_{{ $module->id }}" class="ml-2 font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $module->name }}
                                                </label>
                                            </div>
                                            <div class="ml-6 grid grid-cols-2 gap-3 default-module-permissions" style="display: {{ in_array($module->id, $defaultDriverModules ?? []) ? 'grid' : 'none' }};">
                                                <label class="flex items-center">
                                                    <input 
                                                        type="checkbox" 
                                                        name="modules[{{ $module->id }}][can_view]" 
                                                        value="1"
                                                        class="rounded border-gray-300"
                                                        {{ isset($defaultDriverModulePermissions[$module->id]['can_view']) && $defaultDriverModulePermissions[$module->id]['can_view'] ? 'checked' : '' }}
                                                    >
                                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Visualizar</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input 
                                                        type="checkbox" 
                                                        name="modules[{{ $module->id }}][can_create]" 
                                                        value="1"
                                                        class="rounded border-gray-300"
                                                        {{ isset($defaultDriverModulePermissions[$module->id]['can_create']) && $defaultDriverModulePermissions[$module->id]['can_create'] ? 'checked' : '' }}
                                                    >
                                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Criar</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input 
                                                        type="checkbox" 
                                                        name="modules[{{ $module->id }}][can_edit]" 
                                                        value="1"
                                                        class="rounded border-gray-300"
                                                        {{ isset($defaultDriverModulePermissions[$module->id]['can_edit']) && $defaultDriverModulePermissions[$module->id]['can_edit'] ? 'checked' : '' }}
                                                    >
                                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Editar</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input 
                                                        type="checkbox" 
                                                        name="modules[{{ $module->id }}][can_delete]" 
                                                        value="1"
                                                        class="rounded border-gray-300"
                                                        {{ isset($defaultDriverModulePermissions[$module->id]['can_delete']) && $defaultDriverModulePermissions[$module->id]['can_delete'] ? 'checked' : '' }}
                                                    >
                                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Excluir</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex items-center justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <x-primary-button>
                                        {{ __('Salvar Configurações') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Email -->
                    <div x-show="activeTab === 'email'" x-transition>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Configurações de Notificações por Email</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Configure o envio de notificações por email. Quando habilitado, todas as notificações do sistema serão enviadas por email para os usuários.
                        </p>
                        
                        <form method="POST" action="{{ route('settings.updateEmailSettings') }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-6">
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-6">
                                    <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">
                                        📧 Informações Importantes
                                    </h4>
                                    <div class="text-sm text-blue-800 dark:text-blue-200 space-y-2">
                                        <p>
                                            <strong>Configuração do Servidor:</strong> As configurações do servidor de email (SMTP) devem ser configuradas no arquivo <code>.env</code> do sistema.
                                        </p>
                                        <p>
                                            <strong>Variáveis necessárias:</strong>
                                        </p>
                                        <ul class="list-disc list-inside ml-4 space-y-1">
                                            <li><code>MAIL_MAILER</code> - Tipo de mailer (smtp, sendmail, etc.)</li>
                                            <li><code>MAIL_HOST</code> - Servidor SMTP</li>
                                            <li><code>MAIL_PORT</code> - Porta do servidor</li>
                                            <li><code>MAIL_USERNAME</code> - Usuário do email</li>
                                            <li><code>MAIL_PASSWORD</code> - Senha do email</li>
                                            <li><code>MAIL_ENCRYPTION</code> - Tipo de criptografia (tls, ssl)</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ 
                                    emailEnabled: {{ old('email_notifications_enabled', $settings['email']['email_notifications_enabled'] ?? '0') === '1' ? 'true' : 'false' }},
                                    testingEmail: false,
                                    testMessage: '',
                                    testMessageType: '',
                                    async testEmailConfiguration() {
                                        if (!this.emailEnabled) {
                                            return;
                                        }
                                        
                                        this.testingEmail = true;
                                        this.testMessage = '';
                                        this.testMessageType = '';
                                        
                                        // Coletar dados do formulário
                                        const formData = {
                                            email_from_address: document.getElementById('email_from_address').value,
                                            email_from_name: document.getElementById('email_from_name').value,
                                            mail_mailer: document.getElementById('mail_mailer').value,
                                            mail_host: document.getElementById('mail_host').value,
                                            mail_port: document.getElementById('mail_port').value,
                                            mail_encryption: document.getElementById('mail_encryption').value,
                                            mail_username: document.getElementById('mail_username').value,
                                            mail_password: document.getElementById('mail_password').value,
                                        };
                                        
                                        try {
                                            const response = await fetch('{{ route('settings.testEmailSettings') }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                },
                                                body: JSON.stringify(formData),
                                            });
                                            
                                            const data = await response.json();
                                            
                                            this.testMessage = data.message;
                                            this.testMessageType = data.success ? 'success' : 'error';
                                            
                                            // Scroll para a mensagem
                                            setTimeout(() => {
                                                const messageDiv = document.getElementById('test-email-message');
                                                if (messageDiv) {
                                                    messageDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                                }
                                            }, 100);
                                            
                                            // Limpar mensagem após 10 segundos
                                            if (data.success) {
                                                setTimeout(() => {
                                                    this.testMessage = '';
                                                    this.testMessageType = '';
                                                }, 10000);
                                            }
                                        } catch (error) {
                                            this.testMessage = 'Erro ao testar configuração: ' + error.message;
                                            this.testMessageType = 'error';
                                        } finally {
                                            this.testingEmail = false;
                                        }
                                    }
                                }">
                                    <div class="md:col-span-2">
                                        <div class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                id="email_notifications_enabled" 
                                                name="email_notifications_enabled" 
                                                value="1"
                                                x-model="emailEnabled"
                                                class="rounded border-gray-300 dark:border-gray-700"
                                                {{ old('email_notifications_enabled', $settings['email']['email_notifications_enabled'] ?? '0') === '1' ? 'checked' : '' }}
                                            >
                                            <x-input-label for="email_notifications_enabled" :value="__('Habilitar Notificações por Email')" class="ml-2" />
                                        </div>
                                        <x-input-error :messages="$errors->get('email_notifications_enabled')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Quando habilitado, todas as notificações do sistema serão enviadas por email para os usuários que possuem email cadastrado.
                                        </p>
                                    </div>

                                    <div>
                                        <x-input-label for="email_from_address" :value="__('Email Remetente')" />
                                        <x-text-input 
                                            id="email_from_address" 
                                            class="block mt-1 w-full" 
                                            x-bind:class="emailEnabled ? '' : 'opacity-50 cursor-not-allowed'"
                                            type="email" 
                                            name="email_from_address" 
                                            :value="old('email_from_address', $settings['email']['email_from_address'] ?? config('mail.from.address', 'noreply@example.com'))" 
                                            x-bind:disabled="!emailEnabled"
                                            x-bind:required="emailEnabled"
                                        />
                                        <x-input-error :messages="$errors->get('email_from_address')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Endereço de email que aparecerá como remetente nas notificações.
                                        </p>
                                    </div>

                                    <div>
                                        <x-input-label for="email_from_name" :value="__('Nome do Remetente')" />
                                        <x-text-input 
                                            id="email_from_name" 
                                            class="block mt-1 w-full" 
                                            x-bind:class="emailEnabled ? '' : 'opacity-50 cursor-not-allowed'"
                                            type="text" 
                                            name="email_from_name" 
                                            :value="old('email_from_name', $settings['email']['email_from_name'] ?? config('mail.from.name', $settings['general']['app_name'] ?? 'Road Master'))" 
                                            x-bind:disabled="!emailEnabled"
                                            x-bind:required="emailEnabled"
                                        />
                                        <x-input-error :messages="$errors->get('email_from_name')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Nome que aparecerá como remetente nas notificações.
                                        </p>
                                    </div>

                                    <!-- Mensagem de resultado do teste -->
                                    <div id="test-email-message" x-show="testMessage" x-transition class="md:col-span-2">
                                        <div 
                                            x-bind:class="{
                                                'bg-green-100 dark:bg-green-800 border-green-400 dark:border-green-600 text-green-700 dark:text-green-300': testMessageType === 'success',
                                                'bg-red-100 dark:bg-red-800 border-red-400 dark:border-red-600 text-red-700 dark:text-red-300': testMessageType === 'error'
                                            }"
                                            class="border px-4 py-3 rounded relative mb-4"
                                            role="alert"
                                        >
                                            <span class="block sm:inline" x-text="testMessage"></span>
                                            <button 
                                                @click="testMessage = ''; testMessageType = ''"
                                                class="absolute top-0 bottom-0 right-0 px-4 py-3"
                                            >
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Mensagem de resultado do teste -->
                                    <div id="test-email-message" x-show="testMessage" x-transition class="md:col-span-2">
                                        <div 
                                            x-bind:class="{
                                                'bg-green-100 dark:bg-green-800 border-green-400 dark:border-green-600 text-green-700 dark:text-green-300': testMessageType === 'success',
                                                'bg-red-100 dark:bg-red-800 border-red-400 dark:border-red-600 text-red-700 dark:text-red-300': testMessageType === 'error'
                                            }"
                                            class="border px-4 py-3 rounded relative mb-4"
                                            role="alert"
                                        >
                                            <span class="block sm:inline" x-text="testMessage"></span>
                                            <button 
                                                @click="testMessage = ''; testMessageType = ''"
                                                class="absolute top-0 bottom-0 right-0 px-4 py-3"
                                            >
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Configurações SMTP -->
                                    <div class="md:col-span-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <h4 class="text-md font-semibold text-gray-900 dark:text-gray-100 mb-4">Configurações do Servidor SMTP</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                            Configure as informações do servidor de email. Essas configurações serão salvas no arquivo <code>.env</code> do sistema.
                                        </p>
                                    </div>

                                    <div>
                                        <x-input-label for="mail_mailer" :value="__('Tipo de Mailer')" />
                                        <select 
                                            id="mail_mailer" 
                                            name="mail_mailer" 
                                            class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm"
                                            x-bind:class="emailEnabled ? '' : 'opacity-50 cursor-not-allowed'"
                                            x-bind:disabled="!emailEnabled"
                                            x-bind:required="emailEnabled"
                                        >
                                            <option value="smtp" {{ old('mail_mailer', env('MAIL_MAILER', 'smtp')) === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                            <option value="sendmail" {{ old('mail_mailer', env('MAIL_MAILER')) === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                            <option value="log" {{ old('mail_mailer', env('MAIL_MAILER')) === 'log' ? 'selected' : '' }}>Log (apenas para testes)</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('mail_mailer')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Tipo de mailer utilizado para envio de emails.
                                        </p>
                                    </div>

                                    <div>
                                        <x-input-label for="mail_host" :value="__('Servidor SMTP')" />
                                        <x-text-input 
                                            id="mail_host" 
                                            class="block mt-1 w-full" 
                                            x-bind:class="emailEnabled ? '' : 'opacity-50 cursor-not-allowed'"
                                            type="text" 
                                            name="mail_host" 
                                            :value="old('mail_host', env('MAIL_HOST', 'smtp.mailtrap.io'))" 
                                            x-bind:disabled="!emailEnabled"
                                            x-bind:required="emailEnabled"
                                        />
                                        <x-input-error :messages="$errors->get('mail_host')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Endereço do servidor SMTP (ex: smtp.gmail.com, smtp.mailtrap.io).
                                        </p>
                                    </div>

                                    <div>
                                        <x-input-label for="mail_port" :value="__('Porta do Servidor')" />
                                        <x-text-input 
                                            id="mail_port" 
                                            class="block mt-1 w-full" 
                                            x-bind:class="emailEnabled ? '' : 'opacity-50 cursor-not-allowed'"
                                            type="number" 
                                            name="mail_port" 
                                            :value="old('mail_port', env('MAIL_PORT', '2525'))" 
                                            x-bind:disabled="!emailEnabled"
                                            x-bind:required="emailEnabled"
                                        />
                                        <x-input-error :messages="$errors->get('mail_port')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Porta do servidor SMTP (geralmente 587 para TLS, 465 para SSL, ou 2525 para Mailtrap).
                                        </p>
                                    </div>

                                    <div>
                                        <x-input-label for="mail_encryption" :value="__('Tipo de Criptografia')" />
                                        <select 
                                            id="mail_encryption" 
                                            name="mail_encryption" 
                                            class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm"
                                            x-bind:class="emailEnabled ? '' : 'opacity-50 cursor-not-allowed'"
                                            x-bind:disabled="!emailEnabled"
                                        >
                                            <option value="">Nenhuma</option>
                                            <option value="tls" {{ old('mail_encryption', env('MAIL_ENCRYPTION', 'tls')) === 'tls' ? 'selected' : '' }}>TLS</option>
                                            <option value="ssl" {{ old('mail_encryption', env('MAIL_ENCRYPTION')) === 'ssl' ? 'selected' : '' }}>SSL</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('mail_encryption')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Tipo de criptografia utilizada (TLS é recomendado para porta 587, SSL para porta 465).
                                        </p>
                                    </div>

                                    <div>
                                        <x-input-label for="mail_username" :value="__('Usuário do Email')" />
                                        <x-text-input 
                                            id="mail_username" 
                                            class="block mt-1 w-full" 
                                            x-bind:class="emailEnabled ? '' : 'opacity-50 cursor-not-allowed'"
                                            type="text" 
                                            name="mail_username" 
                                            :value="old('mail_username', env('MAIL_USERNAME', ''))" 
                                            x-bind:disabled="!emailEnabled"
                                        />
                                        <x-input-error :messages="$errors->get('mail_username')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Nome de usuário para autenticação no servidor SMTP.
                                        </p>
                                    </div>

                                    <div>
                                        <x-input-label for="mail_password" :value="__('Senha do Email')" />
                                        <x-text-input 
                                            id="mail_password" 
                                            class="block mt-1 w-full" 
                                            x-bind:class="emailEnabled ? '' : 'opacity-50 cursor-not-allowed'"
                                            type="password" 
                                            name="mail_password" 
                                            :value="''" 
                                            x-bind:disabled="!emailEnabled"
                                            placeholder="Deixe em branco para manter a senha atual"
                                        />
                                        <x-input-error :messages="$errors->get('mail_password')" class="mt-2" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Senha para autenticação no servidor SMTP. Deixe em branco para manter a senha atual.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <button 
                                        type="button"
                                        id="test-email-btn"
                                        x-show="emailEnabled"
                                        x-transition
                                        @click="testEmailConfiguration()"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
                                        :disabled="testingEmail"
                                    >
                                        <svg x-show="!testingEmail" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <svg x-show="testingEmail" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-text="testingEmail ? 'Enviando...' : 'Testar Configuração'"></span>
                                    </button>
                                    <x-primary-button>
                                        {{ __('Salvar Configurações') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tab: Notificações -->
                    <div x-show="activeTab === 'notifications'" x-transition>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Configurações de Notificações Automáticas</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Configure as notificações automáticas de revisão e obrigações legais do sistema.
                        </p>
                        
                        <form method="POST" action="{{ route('settings.updateReviewAndMandatoryEvents') }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-6">
                                <!-- Configurações Gerais -->
                                <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-6">
                                    <h4 class="text-lg font-semibold text-indigo-900 dark:text-indigo-100 mb-4">
                                        🔔 Configurações Gerais
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="md:col-span-2">
                                            <div class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    id="notifications_enabled" 
                                                    name="notifications_enabled" 
                                                    value="1"
                                                    class="rounded border-gray-300 dark:border-gray-700"
                                                    {{ old('notifications_enabled', $settings['notifications']['notifications_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                                                >
                                                <x-input-label for="notifications_enabled" :value="__('Habilitar Notificações Automáticas')" class="ml-2" />
                </div>
                                            <x-input-error :messages="$errors->get('notifications_enabled')" class="mt-2" />
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Quando habilitado, o sistema verificará automaticamente revisões e obrigações legais.
                                            </p>
                                        </div>

                                        <div>
                                            <x-input-label for="notification_check_frequency" :value="__('Frequência de Verificação')" />
                                            <select 
                                                id="notification_check_frequency" 
                                                name="notification_check_frequency" 
                                                class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm"
                                            >
                                                <option value="daily" {{ old('notification_check_frequency', $settings['notifications']['notification_check_frequency'] ?? 'daily') === 'daily' ? 'selected' : '' }}>Diariamente</option>
                                                <option value="weekly" {{ old('notification_check_frequency', $settings['notifications']['notification_check_frequency'] ?? 'daily') === 'weekly' ? 'selected' : '' }}>Semanalmente</option>
                                            </select>
                                            <x-input-error :messages="$errors->get('notification_check_frequency')" class="mt-2" />
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Com que frequência o sistema deve verificar notificações.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Configurações de Revisão -->
                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
                                    <h4 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-4">
                                        🔧 Notificações de Revisão
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <x-input-label for="review_notification_km_before" :value="__('KM de Antecedência')" />
                                            <x-text-input 
                                                id="review_notification_km_before" 
                                                class="block mt-1 w-full" 
                                                type="number" 
                                                name="review_notification_km_before" 
                                                :value="old('review_notification_km_before', $settings['reviews']['review_notification_km_before'] ?? '0')" 
                                                min="0" 
                                                max="100000" 
                                                required 
                                            />
                                            <x-input-error :messages="$errors->get('review_notification_km_before')" class="mt-2" />
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Quantos KM antes do KM configurado a notificação será enviada. Use 0 para notificar no KM exato.
                                            </p>
                                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                                Exemplo: Se configurar 1000, a notificação será enviada quando o veículo atingir (KM configurado - 1000).
                                            </p>
                                        </div>

                                        <div>
                                            <x-input-label for="review_check_time" :value="__('Horário de Verificação')" />
                                            <x-text-input 
                                                id="review_check_time" 
                                                class="block mt-1 w-full" 
                                                type="time" 
                                                name="review_check_time" 
                                                :value="old('review_check_time', $settings['reviews']['review_check_time'] ?? '08:00')" 
                                                required 
                                            />
                                            <x-input-error :messages="$errors->get('review_check_time')" class="mt-2" />
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Horário em que o sistema verificará revisões diariamente.
                                            </p>
                                        </div>

                                        <div class="md:col-span-2">
                                            <div class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    id="review_notify_only_admins" 
                                                    name="review_notify_only_admins" 
                                                    value="1"
                                                    class="rounded border-gray-300 dark:border-gray-700"
                                                    {{ old('review_notify_only_admins', $settings['reviews']['review_notify_only_admins'] ?? '0') === '1' ? 'checked' : '' }}
                                                >
                                                <x-input-label for="review_notify_only_admins" :value="__('Notificar Apenas Administradores')" class="ml-2" />
                                            </div>
                                            <x-input-error :messages="$errors->get('review_notify_only_admins')" class="mt-2" />
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Quando marcado, apenas administradores receberão notificações de revisão. Caso contrário, todos os usuários vinculados ao veículo e os administradores serão notificados.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Configurações de Obrigações Legais -->
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                                    <h4 class="text-lg font-semibold text-yellow-900 dark:text-yellow-100 mb-4">
                                        ⚖️ Obrigações Legais (IPVA, Licenciamento, Multas)
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <x-input-label for="mandatory_event_days_before" :value="__('Dias de Antecedência')" />
                                            <x-text-input 
                                                id="mandatory_event_days_before" 
                                                class="block mt-1 w-full" 
                                                type="number" 
                                                name="mandatory_event_days_before" 
                                                :value="old('mandatory_event_days_before', $settings['mandatory_events']['mandatory_event_days_before'] ?? '10')" 
                                                min="1" 
                                                max="365" 
                                                required 
                                            />
                                            <x-input-error :messages="$errors->get('mandatory_event_days_before')" class="mt-2" />
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Quantos dias antes do vencimento a notificação será enviada.
                                            </p>
                                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                                Exemplo: Se configurar 10, a notificação será enviada 10 dias antes do vencimento.
                                            </p>
                                        </div>

                                        <div>
                                            <x-input-label for="mandatory_event_check_time" :value="__('Horário de Verificação')" />
                                            <x-text-input 
                                                id="mandatory_event_check_time" 
                                                class="block mt-1 w-full" 
                                                type="time" 
                                                name="mandatory_event_check_time" 
                                                :value="old('mandatory_event_check_time', $settings['mandatory_events']['mandatory_event_check_time'] ?? '08:00')" 
                                                required 
                                            />
                                            <x-input-error :messages="$errors->get('mandatory_event_check_time')" class="mt-2" />
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Horário em que o sistema verificará obrigações legais diariamente.
                                            </p>
                                        </div>

                                        <div class="md:col-span-2">
                                            <div class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    id="mandatory_event_notify_only_admins" 
                                                    name="mandatory_event_notify_only_admins" 
                                                    value="1"
                                                    class="rounded border-gray-300 dark:border-gray-700"
                                                    {{ old('mandatory_event_notify_only_admins', $settings['mandatory_events']['mandatory_event_notify_only_admins'] ?? '0') === '1' ? 'checked' : '' }}
                                                >
                                                <x-input-label for="mandatory_event_notify_only_admins" :value="__('Notificar Apenas Administradores')" class="ml-2" />
                                            </div>
                                            <x-input-error :messages="$errors->get('mandatory_event_notify_only_admins')" class="mt-2" />
                                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                Quando marcado, apenas administradores receberão notificações de obrigações legais. Caso contrário, todos os usuários vinculados ao veículo e os administradores serão notificados.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informações Adicionais -->
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                                    <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">
                                        ℹ️ Informações Importantes
                                    </h4>
                                    <div class="text-sm text-blue-800 dark:text-blue-200 space-y-2">
                                        <p>
                                            <strong>Verificação Automática:</strong> Os comandos são executados automaticamente pelo agendador do Laravel (cron/scheduler).
                                        </p>
                                        <p>
                                            <strong>Verificação Manual:</strong> Você pode executar manualmente a qualquer momento:
                                        </p>
                                        <ul class="list-disc list-inside ml-4 space-y-1">
                                            <li><code>php artisan reviews:check</code> - Verificar revisões</li>
                                            <li><code>php artisan mandatory-events:check</code> - Verificar obrigações legais</li>
                                        </ul>
                                        <p>
                                            <strong>Nota:</strong> As alterações de horário requerem que o agendador seja atualizado. O sistema tentará ajustar automaticamente, mas pode ser necessário reiniciar o scheduler.
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                                    <x-primary-button>
                                        {{ __('Salvar Configurações') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function resetAppearance() {
            let confirmed = false;
            if (window.showConfirm) {
                confirmed = await window.showConfirm('Tem certeza que deseja redefinir as logos para o padrão do Laravel?', 'Redefinir Logos');
            } else {
                confirmed = confirm('Tem certeza que deseja redefinir as logos para o padrão do Laravel?');
            }
            if (confirmed) {
                document.getElementById('resetAppearanceForm').submit();
            }
        }

        // Script para gerenciar checkboxes de módulos padrão
        document.addEventListener('DOMContentLoaded', function() {
            const defaultModuleCheckboxes = document.querySelectorAll('.default-module-checkbox');
            
            defaultModuleCheckboxes.forEach(checkbox => {
                const permissionsDiv = checkbox.closest('.border').querySelector('.default-module-permissions');
                
                function togglePermissions() {
                    if (checkbox.checked) {
                        permissionsDiv.style.display = 'grid';
                    } else {
                        permissionsDiv.style.display = 'none';
                        permissionsDiv.querySelectorAll('input[type="checkbox"]').forEach(perm => {
                            perm.checked = false;
                        });
                    }
                }
                
                togglePermissions();
                checkbox.addEventListener('change', togglePermissions);
            });
        });
    </script>
</x-app-layout>
