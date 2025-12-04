<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Importação de Planilhas de KM') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
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
    </div>
</x-app-layout>

