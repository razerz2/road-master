<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            @php
                $appName = \App\Models\SystemSetting::get('app_name') ?? config('app.name', 'Road Master');
                $moduleName = $currentModule ?? null;
            @endphp
            {{ $appName }}@if($moduleName) - {{ $moduleName }}@endif
        </title>

        <!-- Favicon -->
        @php
            $faviconPath = \App\Models\SystemSetting::get('system_favicon');
            $faviconUrl = $faviconPath ? route('storage.serve', ['path' => $faviconPath]) : asset('favicon.ico');
            $faviconType = $faviconPath ? (pathinfo($faviconPath, PATHINFO_EXTENSION) === 'svg' ? 'image/svg+xml' : (pathinfo($faviconPath, PATHINFO_EXTENSION) === 'png' ? 'image/png' : 'image/x-icon')) : 'image/x-icon';
        @endphp
        <link rel="icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <script>
            // Script inline para evitar flash de conteúdo sem tema
            (function() {
                const theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-40">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="pb-12">
                {{ $slot }}
            </main>
        </div>

        <!-- Confirmation Modal -->
        <div x-data="confirmationModal()" x-init="init()" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" @keydown.escape.window="cancel()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="cancel()"></div>
                
                <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/20 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" x-text="title"></h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="message"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="confirm()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Confirmar
                        </button>
                        <button type="button" @click="cancel()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notifications -->
        <div x-data="toastNotifications()" x-init="init()" class="fixed top-4 right-4 z-50 space-y-2" style="max-width: 400px;">
            <template x-for="(toast, index) in toasts" :key="index">
                <div 
                    x-show="toast.show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-x-full"
                    x-transition:enter-end="opacity-100 transform translate-x-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform translate-x-0"
                    x-transition:leave-end="opacity-0 transform translate-x-full"
                    :class="{
                        'bg-red-50 dark:bg-red-900/20 border-red-500 text-red-800 dark:text-red-200': toast.type === 'error',
                        'bg-green-50 dark:bg-green-900/20 border-green-500 text-green-800 dark:text-green-200': toast.type === 'success',
                        'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-500 text-yellow-800 dark:text-yellow-200': toast.type === 'warning',
                        'bg-blue-50 dark:bg-blue-900/20 border-blue-500 text-blue-800 dark:text-blue-200': toast.type === 'info'
                    }"
                    class="border-l-4 rounded-lg shadow-lg p-4 flex items-start space-x-3"
                >
                    <div class="flex-shrink-0">
                        <svg x-show="toast.type === 'error'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="toast.type === 'success'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="toast.type === 'warning'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="toast.type === 'info'" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium" x-text="toast.message"></p>
                    </div>
                    <button 
                        @click="removeToast(toast.id)"
                        class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <script>
            // Sistema de modal de confirmação
            function confirmationModal() {
                return {
                    show: false,
                    title: 'Confirmação',
                    message: '',
                    resolve: null,
                    init() {
                        const self = this;
                        // Tornar a função disponível globalmente
                        window.showConfirm = function(message, title = 'Confirmação') {
                            return new Promise((resolve) => {
                                self.title = title;
                                self.message = message;
                                self.resolve = resolve;
                                self.show = true;
                            });
                        };
                    },
                    confirm() {
                        if (this.resolve) {
                            this.resolve(true);
                            this.resolve = null;
                        }
                        this.show = false;
                    },
                    cancel() {
                        if (this.resolve) {
                            this.resolve(false);
                            this.resolve = null;
                        }
                        this.show = false;
                    }
                };
            }

            // Sistema de notificações toast
            function toastNotifications() {
                return {
                    toasts: [],
                    init() {
                        // Tornar a função disponível globalmente
                        const self = this;
                        window.showToast = (message, type = 'info', duration = 5000) => {
                            const id = Date.now() + Math.random();
                            self.toasts.push({
                                id: id,
                                message: message,
                                type: type,
                                show: true
                            });
                            
                            if (duration > 0) {
                                setTimeout(() => {
                                    self.removeToast(id);
                                }, duration);
                            }
                        };
                    },
                    removeToast(id) {
                        const index = this.toasts.findIndex(t => t.id === id);
                        if (index > -1) {
                            this.toasts[index].show = false;
                            setTimeout(() => {
                                this.toasts.splice(index, 1);
                            }, 200);
                        }
                    }
                };
            }
            
            // Inicializar funções globais após o DOM estar pronto
            document.addEventListener('DOMContentLoaded', function() {
                // Aguardar Alpine inicializar
                setTimeout(() => {
                    // Fallback para showToast
                    if (typeof window.showToast === 'undefined') {
                        window.showToast = function(message, type = 'info', duration = 5000) {
                            const toastContainer = document.querySelector('[x-data*="toastNotifications"]');
                            if (toastContainer && window.Alpine) {
                                try {
                                    const toastData = window.Alpine.$data(toastContainer);
                                    if (toastData && toastData.toasts) {
                                        const id = Date.now() + Math.random();
                                        toastData.toasts.push({
                                            id: id,
                                            message: message,
                                            type: type,
                                            show: true
                                        });
                                        
                                        if (duration > 0) {
                                            setTimeout(() => {
                                                toastData.removeToast(id);
                                            }, duration);
                                        }
                                        return;
                                    }
                                } catch (e) {
                                    console.warn('Erro ao usar toast:', e);
                                }
                            }
                            // Fallback para alert
                            alert(message);
                        };
                    }

                    // Fallback para showConfirm
                    if (typeof window.showConfirm === 'undefined') {
                        window.showConfirm = function(message, title = 'Confirmação') {
                            const modalContainer = document.querySelector('[x-data*="confirmationModal"]');
                            if (modalContainer && window.Alpine) {
                                try {
                                    const modalData = window.Alpine.$data(modalContainer);
                                    if (modalData) {
                                        return new Promise((resolve) => {
                                            modalData.title = title;
                                            modalData.message = message;
                                            modalData.resolve = resolve;
                                            modalData.show = true;
                                        });
                                    }
                                } catch (e) {
                                    console.warn('Erro ao usar modal:', e);
                                }
                            }
                            // Fallback para confirm nativo
                            return Promise.resolve(confirm(message));
                        };
                    }

                    // Função helper para confirmação de exclusão
                    window.handleDelete = async function(form, message = 'Tem certeza?') {
                        if (window.showConfirm) {
                            const confirmed = await window.showConfirm(message, 'Confirmar Exclusão');
                            if (confirmed) {
                                form.submit();
                            }
                        } else {
                            if (confirm(message)) {
                                form.submit();
                            }
                        }
                    };
                }, 100);
            });
        </script>
    </body>
</html>
