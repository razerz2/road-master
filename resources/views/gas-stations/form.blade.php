@php
    $gasStation = $gasStation ?? null;
    $isEdit = isset($gasStation) && $gasStation !== null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="name" :value="__('Nome')" />
        <x-text-input 
            id="name" 
            class="block mt-1 w-full" 
            type="text" 
            name="name"
            :value="old('name', $isEdit ? $gasStation->name : '')"
            required 
        />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="order" :value="__('Ordem')" />
        <x-text-input 
            id="order" 
            class="block mt-1 w-full" 
            type="number" 
            name="order"
            :value="old('order', $isEdit ? $gasStation->order : 0)"
            min="0"
        />
        <x-input-error :messages="$errors->get('order')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" :value="__('Descrição')" />
        <textarea 
            id="description" 
            class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            rows="3"
            name="description"
        >{{ old('description', $isEdit ? ($gasStation->description ?? '') : '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <label class="flex items-center">
            <input 
                type="checkbox" 
                name="active"
                value="1"
                {{ old('active', $isEdit ? ($gasStation->active ?? true) : true) ? 'checked' : '' }}
                class="rounded border-gray-300"
            >
            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Ativo</span>
        </label>
    </div>
</div>

<div class="flex items-center justify-end mt-4 gap-2">
    @if($isEdit)
        <a 
            href="{{ route('gas-stations.index') }}"
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600"
        >
            Cancelar
        </a>
    @endif
    <x-primary-button type="submit">
        {{ $isEdit ? 'Atualizar' : 'Criar' }}
    </x-primary-button>
</div>

