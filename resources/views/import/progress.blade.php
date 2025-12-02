<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Progresso da Importação') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Informações do Arquivo -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-200">Informações da Importação</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Arquivo:</span>
                                <span class="ml-2 font-medium text-gray-800 dark:text-gray-200">{{ $progress['file_name'] ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Ano:</span>
                                <span class="ml-2 font-medium text-gray-800 dark:text-gray-200">{{ $progress['year'] ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Iniciado em:</span>
                                <span class="ml-2 font-medium text-gray-800 dark:text-gray-200">{{ $progress['started_at'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Barra de Progresso -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status: 
                                <span id="status-text" class="font-semibold">
                                    @if($progress['status'] === 'processing')
                                        Processando...
                                    @elseif($progress['status'] === 'completed')
                                        Concluído
                                    @elseif($progress['status'] === 'error')
                                        Erro
                                    @endif
                                </span>
                            </span>
                            <span id="progress-text" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ $progress['processed'] ?? 0 }} linha(s) processada(s)
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                            <div id="progress-bar" 
                                 class="bg-blue-600 h-4 rounded-full transition-all duration-300 ease-out"
                                 style="width: {{ $progress['status'] === 'completed' ? 100 : ($progress['progress'] ?? 0) }}%">
                            </div>
                        </div>
                    </div>

                    <!-- Aba Atual -->
                    <div id="current-sheet" class="mb-6 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Aba atual:</span>
                        <span class="ml-2 font-medium text-gray-800 dark:text-gray-200">{{ $progress['current_sheet'] ?: 'Aguardando...' }}</span>
                    </div>

                    <!-- Log de Atividades -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">Log de Atividades</h3>
                        <div id="log-container" class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm h-96 overflow-y-auto">
                            @foreach($progress['logs'] ?? [] as $log)
                                <div class="mb-1">
                                    <span class="text-gray-500">[{{ $log['time'] }}]</span>
                                    <span class="ml-2 
                                        @if($log['type'] === 'error') text-red-400
                                        @elseif($log['type'] === 'success') text-green-400
                                        @elseif($log['type'] === 'info') text-blue-400
                                        @else text-gray-300
                                        @endif">
                                        {{ $log['message'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-between items-center">
                        <a href="{{ route('import.index') }}" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                            ← Voltar para Importação
                        </a>
                        @if($progress['status'] === 'completed')
                            <div class="flex gap-3">
                                <a href="{{ route('trips.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Ver Percursos
                                </a>
                                <a href="{{ route('import.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                                    Nova Importação
                                </a>
                            </div>
                        @elseif($progress['status'] === 'error')
                            <a href="{{ route('import.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                                Tentar Novamente
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($progress['status'] === 'processing')
    <script>
        const importId = '{{ $importId }}';
        let lastLogCount = {{ count($progress['logs'] ?? []) }};
        
        function updateProgress() {
            fetch(`/importacao/status/${importId}`)
                .then(response => response.json())
                .then(data => {
                    // Atualizar barra de progresso
                    const progressBar = document.getElementById('progress-bar');
                    const progressText = document.getElementById('progress-text');
                    const statusText = document.getElementById('status-text');
                    const currentSheet = document.getElementById('current-sheet');
                    const logContainer = document.getElementById('log-container');
                    
                    if (data.status === 'completed') {
                        progressBar.style.width = '100%';
                        progressText.textContent = `${data.rows_imported || data.processed || 0} linha(s) importada(s)`;
                        statusText.textContent = 'Concluído';
                        statusText.className = 'font-semibold text-green-600 dark:text-green-400';
                        currentSheet.innerHTML = '<span class="text-sm text-gray-600 dark:text-gray-400">Status:</span><span class="ml-2 font-medium text-green-600 dark:text-green-400">Importação concluída com sucesso!</span>';
                        
                        // Recarregar página após 2 segundos para mostrar botões
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                        return;
                    }
                    
                    if (data.status === 'error') {
                        statusText.textContent = 'Erro';
                        statusText.className = 'font-semibold text-red-600 dark:text-red-400';
                        currentSheet.innerHTML = '<span class="text-sm text-gray-600 dark:text-gray-400">Erro:</span><span class="ml-2 font-medium text-red-600 dark:text-red-400">' + (data.error || 'Erro desconhecido') + '</span>';
                        return;
                    }
                    
                    // Atualizar progresso
                    const processed = data.processed || 0;
                    const progressPercent = data.progress || 0;
                    progressText.textContent = `${processed} linha(s) processada(s)`;
                    
                    // Atualizar barra de progresso
                    if (progressBar) {
                        progressBar.style.width = `${progressPercent}%`;
                    }
                    
                    // Atualizar aba atual
                    if (data.current_sheet) {
                        currentSheet.innerHTML = '<span class="text-sm text-gray-600 dark:text-gray-400">Aba atual:</span><span class="ml-2 font-medium text-gray-800 dark:text-gray-200">' + data.current_sheet + '</span>';
                    }
                    
                    // Adicionar novos logs
                    if (data.logs && data.logs.length > lastLogCount) {
                        const newLogs = data.logs.slice(lastLogCount);
                        newLogs.forEach(log => {
                            const logDiv = document.createElement('div');
                            logDiv.className = 'mb-1';
                            
                            const timeSpan = document.createElement('span');
                            timeSpan.className = 'text-gray-500';
                            timeSpan.textContent = `[${log.time}]`;
                            
                            const messageSpan = document.createElement('span');
                            messageSpan.className = 'ml-2 ' + (
                                log.type === 'error' ? 'text-red-400' :
                                log.type === 'success' ? 'text-green-400' :
                                log.type === 'info' ? 'text-blue-400' :
                                'text-gray-300'
                            );
                            messageSpan.textContent = log.message;
                            
                            logDiv.appendChild(timeSpan);
                            logDiv.appendChild(messageSpan);
                            logContainer.appendChild(logDiv);
                            
                            // Scroll para o final
                            logContainer.scrollTop = logContainer.scrollHeight;
                        });
                        
                        lastLogCount = data.logs.length;
                    }
                })
                .catch(error => {
                    console.error('Erro ao atualizar progresso:', error);
                });
        }
        
        // Atualizar a cada 1 segundo
        const interval = setInterval(updateProgress, 1000);
        
        // Parar quando a página for fechada
        window.addEventListener('beforeunload', () => {
            clearInterval(interval);
        });
        
        // Primeira atualização imediata
        updateProgress();
    </script>
    @endif
</x-app-layout>

