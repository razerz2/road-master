<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Foto de Perfil -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Foto de Perfil') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Atualize sua foto de perfil. Você pode fazer upload de uma imagem ou tirar uma foto com a webcam.') }}
                        </p>
                    </header>

                    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6" id="avatarForm">
                        @csrf
                        @method('patch')

                        <div class="space-y-4">
                            <!-- Preview da imagem -->
                            <div class="flex items-center space-x-6">
                                <div class="relative">
                                    @php
                                        $userAvatar = $user->avatar ? route('storage.serve', ['path' => $user->avatar]) : null;
                                    @endphp
                                    <img id="avatarPreview" src="{{ $userAvatar ?? '' }}" alt="Preview" class="w-32 h-32 rounded-full object-cover border-4 border-gray-300 dark:border-gray-600 shadow-lg {{ $userAvatar ? '' : 'hidden' }}">
                                    <div id="avatarPlaceholder" class="w-32 h-32 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-4xl font-semibold border-4 border-gray-300 dark:border-gray-600 shadow-lg {{ $userAvatar ? 'hidden' : '' }}">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
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
                                    <button type="button" id="removeAvatarBtn" onclick="removeAvatar()" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition {{ $userAvatar ? '' : 'hidden' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Redefinir para Padrão
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

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Salvar Foto') }}</x-primary-button>

                            @if (session('status') === 'profile-updated')
                                <p
                                    x-data="{ show: true }"
                                    x-show="show"
                                    x-transition
                                    x-init="setTimeout(() => show = false, 2000)"
                                    class="text-sm text-gray-600 dark:text-gray-400"
                                >{{ __('Salvo.') }}</p>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    <script>
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
