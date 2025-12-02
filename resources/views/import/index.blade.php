<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Importação de Planilhas de KM') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

