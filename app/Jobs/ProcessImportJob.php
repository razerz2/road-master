<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KMImport;
use App\Models\ImportLog;
use App\Models\Trip;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessImportJob implements ShouldQueue
{
    use Queueable;

    protected $filePath;
    protected $fileName;
    protected $year;
    protected $vehicleId;
    protected $importId;
    protected $sheetNames;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $fileName, $year, $vehicleId, $importId, $sheetNames, $userId)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->year = $year;
        $this->vehicleId = $vehicleId;
        $this->importId = $importId;
        $this->sheetNames = $sheetNames;
        $this->userId = (int) $userId; // Garantir que seja um inteiro
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('ProcessImportJob iniciado', [
            'import_id' => $this->importId,
            'file_path' => $this->filePath,
            'user_id' => $this->userId,
        ]);
        
        try {
            // Log inicial
            $progress = Cache::get("import_progress_{$this->importId}", []);
            $progress['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'info',
                'message' => '[JOB] ProcessImportJob.handle() iniciado',
            ];
            Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));
            
            // Validar userId
            if (!$this->userId) {
                $errorMsg = 'ID do usuário não fornecido. Não é possível processar a importação.';
                \Log::error('ProcessImportJob: userId não fornecido', ['import_id' => $this->importId]);
                
                $progress = Cache::get("import_progress_{$this->importId}", []);
                $progress['status'] = 'error';
                $progress['error'] = $errorMsg;
                $progress['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'error',
                    'message' => '[JOB] ' . $errorMsg,
                ];
                Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));
                throw new \Exception($errorMsg);
            }

            // Atualizar progresso inicial
            $progress = Cache::get("import_progress_{$this->importId}", []);
            $progress['status'] = 'processing';
            $progress['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'info',
                'message' => '[JOB] Iniciando processamento da importação... (Usuário ID: ' . $this->userId . ')',
            ];
            Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));

            // Obter caminho absoluto do arquivo
            $absolutePath = Storage::disk('local')->path($this->filePath);

            // Garantir que userId esteja no cache antes de processar
            $progress = Cache::get("import_progress_{$this->importId}", []);
            $progress['user_id'] = $this->userId;
            Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));
            
            // Registrar esta importação como ativa para o observer do Trip
            $activeImports = Cache::get('active_imports', []);
            if (!in_array($this->importId, $activeImports)) {
                $activeImports[] = $this->importId;
                Cache::put('active_imports', $activeImports, now()->addHours(1));
            }
            
            // Atualizar progresso antes de iniciar importação
            $progress = Cache::get("import_progress_{$this->importId}", []);
            $progress['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'info',
                'message' => 'Criando instância de importação...',
            ];
            Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));

            // Verificar se arquivo existe
            if (!file_exists($absolutePath)) {
                throw new \Exception("Arquivo não encontrado: {$absolutePath}");
            }
            
            $progress = Cache::get("import_progress_{$this->importId}", []);
            $progress['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'info',
                'message' => "Arquivo encontrado. Tamanho: " . filesize($absolutePath) . " bytes",
            ];
            Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));

            // Criar instância do KMImport
            $kmImport = new KMImport(
                year: $this->year,
                vehicleId: $this->vehicleId,
                importId: $this->importId,
                sheetNames: $this->sheetNames,
                userId: $this->userId
            );

            // Obter instância de SheetTripsImport (agora processamos diretamente sem WithMultipleSheets)
            $import = $kmImport->getSheetImport();

            // Atualizar progresso antes de processar
            $progress = Cache::get("import_progress_{$this->importId}", []);
            $progress['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'info',
                'message' => 'Iniciando leitura do arquivo Excel... (processamento direto)',
            ];
            Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));

            // Processar importação com tratamento de erro detalhado
            try {
                // Obter o nome da aba a ser processada
                $sheetName = !empty($this->sheetNames) ? $this->sheetNames[0] : null;
                
                $progress = Cache::get("import_progress_{$this->importId}", []);
                $progress['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'info',
                    'message' => 'Chamando Excel::import() diretamente com SheetTripsImport...' . ($sheetName ? " (Aba: {$sheetName})" : ' (primeira aba)'),
                ];
                Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));
                
                // Validar que o arquivo existe e pode ser lido
                if (!is_readable($absolutePath)) {
                    throw new \Exception("Arquivo não pode ser lido: {$absolutePath}");
                }
                
                // Processar importação - o Excel::import() processa a primeira aba por padrão
                // Com WithStartRow, ele começará da linha 2 (pulando cabeçalho)
                Excel::import($import, $absolutePath);
                
                // Atualizar progresso após processar
                $progress = Cache::get("import_progress_{$this->importId}", []);
                $progress['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'info',
                    'message' => 'Excel::import() concluído. Contando registros importados...',
                ];
                Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                $progress = Cache::get("import_progress_{$this->importId}", []);
                $errorMsg = 'Erro ao ler arquivo Excel: ' . $e->getMessage() . '. Verifique se o arquivo está no formato correto (.xlsx ou .xls) e não está corrompido.';
                $progress['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'error',
                    'message' => $errorMsg,
                ];
                $progress['status'] = 'error';
                $progress['error'] = $errorMsg;
                Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));
                throw new \Exception($errorMsg, 0, $e);
            } catch (\Exception $e) {
                $progress = Cache::get("import_progress_{$this->importId}", []);
                $errorMsg = 'Erro ao processar Excel: ' . $e->getMessage() . ' | Linha: ' . $e->getLine() . ' | Arquivo: ' . basename($e->getFile());
                $progress['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'error',
                    'message' => $errorMsg . ' | Trace: ' . substr($e->getTraceAsString(), 0, 500),
                ];
                $progress['status'] = 'error';
                $progress['error'] = $errorMsg;
                Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));
                throw $e;
            }

            // Contar linhas importadas
            $rowsImported = Trip::where('vehicle_id', $this->vehicleId)
                ->whereYear('date', $this->year)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->count();

            // Criar log de importação
            ImportLog::create([
                'file_name' => $this->fileName,
                'year' => $this->year,
                'vehicle_id' => $this->vehicleId,
                'rows_imported' => $rowsImported
            ]);

            // Atualizar progresso final
            $progress = Cache::get("import_progress_{$this->importId}", []);
            $progress['status'] = 'completed';
            $progress['progress'] = 100;
            $progress['rows_imported'] = $rowsImported;
            $progress['processed'] = $rowsImported;
            $progress['completed_at'] = now()->toDateTimeString();
            $progress['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'success',
                'message' => "Importação concluída com sucesso! {$rowsImported} linha(s) importada(s).",
            ];
            Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));
            
            // Remover da lista de importações ativas
            $activeImports = Cache::get('active_imports', []);
            $activeImports = array_filter($activeImports, fn($id) => $id !== $this->importId);
            Cache::put('active_imports', $activeImports, now()->addHours(1));

            // Remover arquivo temporário
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
        } catch (\Exception $e) {
            // Atualizar progresso com erro
            $progress = Cache::get("import_progress_{$this->importId}", []);
            $progress['status'] = 'error';
            $progress['error'] = $e->getMessage();
            $progress['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'error',
                'message' => 'Erro na importação: ' . $e->getMessage(),
            ];
            Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));

            // Remover arquivo temporário mesmo em caso de erro
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }

            throw $e;
        }
    }
}
