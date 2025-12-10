<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Importação de Planilhas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Abas de navegação -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button onclick="showTab('trips')" id="tab-trips" class="tab-button active border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600 dark:text-blue-400">
                            Importar Viagens (KM)
                        </button>
                        <button onclick="showTab('locations')" id="tab-locations" class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                            Importar Locais
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Conteúdo da aba de Viagens -->
            <div id="content-trips" class="tab-content">
            <!-- Informações sobre a estrutura da planilha -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4">
                    📋 Estrutura da Planilha
                </h3>
                <div class="space-y-4 text-sm text-blue-800 dark:text-blue-200">
                    <div>
                        <p class="font-medium mb-2">A planilha deve ter a seguinte estrutura na primeira linha (cabeçalho):</p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-blue-300 dark:border-blue-700 rounded-lg">
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

            <!-- Formulário de Importação -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
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
                                <input type="file" name="file" id="file" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" accept=".xlsx,.xls" required>
                                <x-input-error :messages="$errors->get('file')" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-500">Formatos aceitos: .xlsx, .xls</p>
                            </div>

                            <div>
                                <x-input-label for="year" :value="__('Ano da planilha')" />
                                <x-text-input id="year" class="block mt-1 w-full" type="number" name="year" :value="old('year', date('Y'))" min="2000" max="2100" required />
                                <x-input-error :messages="$errors->get('year')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="vehicle_id" :value="__('Veículo para vincular os percursos')" />
                                <select id="vehicle_id" name="vehicle_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
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
            </div>
            </div>

            <!-- Conteúdo da aba de Locais -->
            <div id="content-locations" class="tab-content hidden">
            <!-- Informações sobre a estrutura da planilha de locais -->
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-green-900 dark:text-green-100 mb-4">
                    📋 Importação de Locais
                </h3>
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

            <!-- Formulário de Importação de Locais -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('import.locations') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <x-input-label for="file_locations" :value="__('Selecione o arquivo')" />
                                <input type="file" name="file" id="file_locations" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" accept=".xlsx,.xls" required>
                                <x-input-error :messages="$errors->get('file')" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-500">Formatos aceitos: .xlsx, .xls</p>
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
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Esconder todos os conteúdos
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remover classe active de todas as abas
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
            });
            
            // Mostrar conteúdo selecionado
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Ativar aba selecionada
            const activeButton = document.getElementById('tab-' + tabName);
            activeButton.classList.add('active', 'border-blue-500', 'text-blue-600', 'dark:text-blue-400');
            activeButton.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        }
    </script>
</x-app-layout>

