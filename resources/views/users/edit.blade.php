<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Usuário') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div class="mb-4 bg-red-100 dark:bg-red-800 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            
            @php
                $isCurrentUser = Auth::id() === $user->id;
                $isAdmin = $user->role === 'admin';
            @endphp
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" id="userForm">
                        @csrf
                        @method('PUT')

                        <!-- Foto de Perfil -->
                        <div class="mb-6">
                            <x-input-label for="avatar" :value="__('Foto de Perfil')" />
                            <div class="mt-2 space-y-4">
                                <!-- Preview da imagem -->
                                <div class="flex items-center space-x-6">
                                    <div class="relative">
                                        @php
                                            $avatarUrl = $user->avatar ? route('storage.serve', ['path' => $user->avatar]) : null;
                                        @endphp
                                        <img id="avatarPreview" src="{{ $avatarUrl ?? '' }}" alt="Preview" class="w-32 h-32 rounded-full object-cover border-4 border-gray-300 dark:border-gray-600 {{ $avatarUrl ? '' : 'hidden' }}">
                                        <div id="avatarPlaceholder" class="w-32 h-32 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center border-4 border-gray-300 dark:border-gray-600 {{ $avatarUrl ? 'hidden' : '' }}">
                                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex flex-col space-y-2">
                                        <label for="avatar" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer transition">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            Escolher Imagem
                                        </label>
                                        <input type="file" name="avatar" id="avatar" accept="image/*" class="hidden" onchange="handleFileSelect(event)">
                                        <button type="button" onclick="openWebcam()" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            Tirar Foto
                                        </button>
                                        <button type="button" id="removeAvatarBtn" onclick="removeAvatar()" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition {{ $avatarUrl ? '' : 'hidden' }}">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Remover
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="avatar_base64" id="avatar_base64">
                                <input type="hidden" name="remove_avatar" id="remove_avatar" value="0">
                                <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Formatos aceitos: JPG, JPEG, PNG. Tamanho máximo: 2MB.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Nome (Exibição)')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Nome usado para exibição no sistema</p>
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="name_full" :value="__('Nome Completo')" />
                                <x-text-input id="name_full" class="block mt-1 w-full" type="text" name="name_full" :value="old('name_full', $user->name_full)" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Nome completo do usuário</p>
                                <x-input-error :messages="$errors->get('name_full')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="role" :value="__('Perfil')" />
                                @php
                                    $currentRole = old('role', $user->role);
                                    // Considerar 'motorista' e 'condutor' como equivalentes (para compatibilidade com dados antigos)
                                    $isCondutor = ($currentRole === 'condutor' || $currentRole === 'motorista');
                                    $canChangeRole = !($isCurrentUser && $isAdmin);
                                @endphp
                                <select 
                                    id="role" 
                                    name="role" 
                                    class="block mt-1 w-full rounded-md border-gray-300 shadow-sm {{ !$canChangeRole ? 'opacity-50 cursor-not-allowed bg-gray-100' : '' }}" 
                                    required
                                    {{ !$canChangeRole ? 'disabled' : '' }}
                                >
                                    <option value="condutor" {{ $isCondutor ? 'selected' : '' }}>Condutor</option>
                                    <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                @if(!$canChangeRole)
                                    <input type="hidden" name="role" value="{{ $currentRole }}">
                                    <p class="mt-2 text-sm text-yellow-600 dark:text-yellow-400">
                                        Você não pode alterar seu próprio perfil de administrador.
                                    </p>
                                @endif
                                <x-input-error :messages="$errors->get('role')" class="mt-2" />
                            </div>


                            <div>
                                @php
                                    $canDisable = !($isCurrentUser && $isAdmin);
                                @endphp
                                <label class="flex items-center mt-6">
                                    <input 
                                        type="checkbox" 
                                        name="active" 
                                        value="1" 
                                        {{ old('active', $user->active) ? 'checked' : '' }} 
                                        class="rounded border-gray-300 {{ !$canDisable ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ !$canDisable ? 'disabled' : '' }}
                                    >
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                        Ativo
                                        @if(!$canDisable)
                                            <span class="text-yellow-600 dark:text-yellow-400 text-xs block mt-1">
                                                Você não pode desativar sua própria conta enquanto for administrador.
                                            </span>
                                        @endif
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Veículos Responsáveis -->
                        <div class="mt-8">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Veículos sob Responsabilidade</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Selecione os veículos que este usuário será responsável.
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-60 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                @forelse($vehicles as $vehicle)
                                    <label class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer">
                                        <input 
                                            type="checkbox" 
                                            name="vehicles[]" 
                                            value="{{ $vehicle->id }}"
                                            class="rounded border-gray-300"
                                            {{ in_array($vehicle->id, old('vehicles', $userVehicles)) ? 'checked' : '' }}
                                        >
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $vehicle->name }} - {{ $vehicle->plate }}
                                        </span>
                                    </label>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400 col-span-full">
                                        Nenhum veículo cadastrado.
                                    </p>
                                @endforelse
                            </div>
                            <x-input-error :messages="$errors->get('vehicles')" class="mt-2" />
                        </div>

                        <!-- Módulos de Acesso -->
                        <div class="mt-8" id="modules-section" style="display: {{ old('role', $user->role) === 'admin' ? 'none' : 'block' }};">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Módulos de Acesso</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($modules->where('slug', '!=', 'users') as $module)
                                    @php
                                        $permission = $userPermissions->get($module->id);
                                        $hasAccess = $permission !== null;
                                    @endphp
                                    <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                        <div class="flex items-center mb-3">
                                            <input 
                                                type="checkbox" 
                                                name="modules[{{ $module->id }}][enabled]" 
                                                value="1" 
                                                id="module_{{ $module->id }}"
                                                class="module-checkbox rounded border-gray-300"
                                                {{ old("modules.{$module->id}.enabled", $hasAccess) ? 'checked' : '' }}
                                            >
                                            <label for="module_{{ $module->id }}" class="ml-2 font-medium text-gray-900 dark:text-gray-100">
                                                {{ $module->name }}
                                            </label>
                                        </div>
                                        <div class="ml-6 grid grid-cols-2 gap-3 module-permissions" style="display: {{ $hasAccess ? 'grid' : 'none' }};">
                                            <label class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    name="modules[{{ $module->id }}][can_view]" 
                                                    value="1"
                                                    class="rounded border-gray-300"
                                                    {{ old("modules.{$module->id}.can_view", $permission && $permission->can_view) ? 'checked' : '' }}
                                                >
                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Visualizar</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    name="modules[{{ $module->id }}][can_create]" 
                                                    value="1"
                                                    class="rounded border-gray-300"
                                                    {{ old("modules.{$module->id}.can_create", $permission && $permission->can_create) ? 'checked' : '' }}
                                                >
                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Criar</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    name="modules[{{ $module->id }}][can_edit]" 
                                                    value="1"
                                                    class="rounded border-gray-300"
                                                    {{ old("modules.{$module->id}.can_edit", $permission && $permission->can_edit) ? 'checked' : '' }}
                                                >
                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Editar</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    name="modules[{{ $module->id }}][can_delete]" 
                                                    value="1"
                                                    class="rounded border-gray-300"
                                                    {{ old("modules.{$module->id}.can_delete", $permission && $permission->can_delete) ? 'checked' : '' }}
                                                >
                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Excluir</span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('users.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <x-primary-button>
                                {{ __('Atualizar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const modulesSection = document.getElementById('modules-section');
            
            // Função para mostrar/ocultar módulos baseado no perfil
            function toggleModulesSection() {
                if (roleSelect.value === 'admin') {
                    modulesSection.style.display = 'none';
                } else if (roleSelect.value === 'condutor') {
                    modulesSection.style.display = 'block';
                } else {
                    modulesSection.style.display = 'none';
                }
            }
            
            // Verificar estado inicial
            toggleModulesSection();
            
            // Adicionar listener para mudanças no perfil
            roleSelect.addEventListener('change', toggleModulesSection);
            
            const moduleCheckboxes = document.querySelectorAll('.module-checkbox');
            
            moduleCheckboxes.forEach(checkbox => {
                const permissionsDiv = checkbox.closest('.border').querySelector('.module-permissions');
                
                // Mostrar/ocultar permissões baseado no checkbox
                function togglePermissions() {
                    if (checkbox.checked) {
                        permissionsDiv.style.display = 'grid';
                    } else {
                        permissionsDiv.style.display = 'none';
                        // Desmarcar todas as permissões quando o módulo é desmarcado
                        permissionsDiv.querySelectorAll('input[type="checkbox"]').forEach(perm => {
                            perm.checked = false;
                        });
                    }
                }
                
                // Verificar estado inicial
                togglePermissions();
                
                // Adicionar listener
                checkbox.addEventListener('change', togglePermissions);
            });
        });

        // Função para lidar com seleção de arquivo
        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                    document.getElementById('avatarPreview').classList.remove('hidden');
                    document.getElementById('avatarPlaceholder').classList.add('hidden');
                    document.getElementById('removeAvatarBtn').classList.remove('hidden');
                    document.getElementById('avatar_base64').value = '';
                    document.getElementById('remove_avatar').value = '0';
                };
                reader.readAsDataURL(file);
            }
        }

        // Função para abrir webcam
        let stream = null;
        let currentModal = null;
        
        function openWebcam() {
            // Criar modal para webcam
            const modal = document.createElement('div');
            modal.id = 'webcamModal';
            modal.className = 'fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50';
            modal.innerHTML = `
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Tirar Foto</h3>
                    <video id="webcamVideo" autoplay playsinline class="w-full rounded-lg mb-4 bg-gray-900"></video>
                    <canvas id="webcamCanvas" class="hidden"></canvas>
                    <div class="flex space-x-2">
                        <button type="button" id="captureBtn" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Capturar
                        </button>
                        <button type="button" id="cancelBtn" class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                            Cancelar
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            currentModal = modal;

            // Adicionar event listeners após o modal ser adicionado ao DOM
            setTimeout(() => {
                const video = document.getElementById('webcamVideo');
                const captureBtn = document.getElementById('captureBtn');
                const cancelBtn = document.getElementById('cancelBtn');
                
                if (!video) {
                    alert('Erro ao inicializar a webcam. Tente novamente.');
                    closeWebcam();
                    return;
                }

                captureBtn.addEventListener('click', capturePhoto);
                cancelBtn.addEventListener('click', closeWebcam);

                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } })
                    .then(function(mediaStream) {
                        stream = mediaStream;
                        video.srcObject = stream;
                    })
                    .catch(function(err) {
                        alert('Erro ao acessar a webcam: ' + err.message);
                        closeWebcam();
                    });
            }, 100);
        }

        function capturePhoto() {
            const video = document.getElementById('webcamVideo');
            const canvas = document.getElementById('webcamCanvas');
            const context = canvas.getContext('2d');
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0);
            
            const dataURL = canvas.toDataURL('image/png');
            document.getElementById('avatar_base64').value = dataURL;
            document.getElementById('avatarPreview').src = dataURL;
            document.getElementById('avatarPreview').classList.remove('hidden');
            document.getElementById('avatarPlaceholder').classList.add('hidden');
            document.getElementById('removeAvatarBtn').classList.remove('hidden');
            document.getElementById('avatar').value = '';
            document.getElementById('remove_avatar').value = '0';
            
            closeWebcam();
        }

        function closeWebcam() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            if (currentModal) {
                document.body.removeChild(currentModal);
                currentModal = null;
            }
        }

        function removeAvatar() {
            document.getElementById('avatarPreview').src = '';
            document.getElementById('avatarPreview').classList.add('hidden');
            document.getElementById('avatarPlaceholder').classList.remove('hidden');
            document.getElementById('removeAvatarBtn').classList.add('hidden');
            document.getElementById('avatar').value = '';
            document.getElementById('avatar_base64').value = '';
            document.getElementById('remove_avatar').value = '1';
        }
    </script>
</x-app-layout>

