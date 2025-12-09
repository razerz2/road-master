<?php

namespace App\Imports;

use App\Models\Location;
use App\Models\LocationType;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SheetLocationsImport implements ToModel, WithChunkReading, WithEvents, SkipsEmptyRows, WithStartRow
{
    protected $importId;
    protected $processedRows = 0;
    protected $rowsImported = 0;
    protected $sheetName;
    protected $stopProcessing = false;
    protected $userId;

    public function __construct($importId = null, $sheetName = null, $userId = null)
    {
        $this->importId = $importId ?? uniqid('import_', true);
        $this->sheetName = $sheetName;
        
        // Obter userId
        if ($userId) {
            $this->userId = (int) $userId;
        } elseif (auth()->id()) {
            $this->userId = (int) auth()->id();
        } else {
            $progress = Cache::get("import_progress_{$importId}", []);
            $this->userId = isset($progress['user_id']) ? (int) $progress['user_id'] : null;
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function startRow(): int
    {
        // Dados começam na linha 2 (linha 1 é cabeçalho)
        return 2;
    }

    public function getImportId(): string
    {
        return $this->importId;
    }

    /**
     * Verifica se a linha está vazia
     */
    private function isRowEmpty($row)
    {
        foreach ($row as $value) {
            if (!empty($value) && trim($value) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Padroniza o nome de um local para evitar duplicidades
     */
    private function normalizeLocationName($name)
    {
        if (empty($name)) {
            return null;
        }

        // Trim
        $name = trim($name);

        // Remover espaços duplos
        $name = preg_replace('/\s+/', ' ', $name);

        // Converter para lowercase e depois ucwords
        $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');

        return $name;
    }

    /**
     * Remove acentos de uma string para comparação
     */
    private function removeAccents($string)
    {
        if (empty($string)) {
            return '';
        }
        
        $accents = [
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ç' => 'C', 'ç' => 'c',
            'Ñ' => 'N', 'ñ' => 'n',
            'Ý' => 'Y', 'ý' => 'y', 'ÿ' => 'y',
        ];
        
        return strtr($string, $accents);
    }

    /**
     * Busca ou cria um local considerando acentos como equivalentes
     * Mesma lógica usada no SheetTripsImport
     */
    private function findOrCreateLocationByName($name)
    {
        if (empty($name)) {
            return null;
        }

        // Normalizar o nome (preservando caracteres para exibição)
        $normalizedName = $this->normalizeLocationName($name);
        
        if (!$normalizedName) {
            return null;
        }

        // Criar versão sem acentos para comparação
        $nameWithoutAccents = $this->removeAccents(strtolower(trim($normalizedName)));

        // Buscar todos os locais e comparar ignorando acentos
        $allLocations = Location::all();
        
        foreach ($allLocations as $location) {
            $locationNameWithoutAccents = $this->removeAccents(strtolower(trim($location->name)));
            
            // Se os nomes sem acentos forem iguais, retornar o local existente
            if ($locationNameWithoutAccents === $nameWithoutAccents) {
                return $location;
            }
        }

        // Se não encontrou, criar novo local com o nome normalizado (preservando caracteres)
        return Location::create(['name' => $normalizedName]);
    }

    /**
     * Lê coluna por índice ou nome
     */
    private function getColumnValue($row, $index, $possibleNames = [])
    {
        // Tentar por índice numérico primeiro
        if (isset($row[$index])) {
            $value = $row[$index];
            if ($value !== null && $value !== '' && trim((string)$value) !== '') {
                return $value;
            }
        }
        // Tentar por nomes possíveis
        foreach ($possibleNames as $name) {
            if (isset($row[$name])) {
                $value = $row[$name];
                if ($value !== null && $value !== '' && trim((string)$value) !== '') {
                    return $value;
                }
            }
        }
        return null;
    }

    public function model(array $row)
    {
        try {
            $this->processedRows++;

            // Verificar se deve parar processamento
            if ($this->stopProcessing) {
                return null;
            }

            // Verificar se a linha está vazia
            if ($this->isRowEmpty($row)) {
                return null;
            }

            // Verificar se encontrou "TOTAL KM RODADOS" - parar leitura
            $itinerario = $row[0] ?? null;
            if (!empty($itinerario) && stripos($itinerario, 'TOTAL KM RODADOS') !== false) {
                $this->stopProcessing = true;
                $this->updateProgress('info', "Encontrado 'TOTAL KM RODADOS' na linha {$this->processedRows}. Finalizando leitura.");
                return null;
            }

            // Ler apenas a coluna A (ITINERÁRIO) - mesma estrutura da planilha de KM
            $itinerario = $this->getColumnValue($row, 0, ['ITINERÁRIO', 'itinerario', 'Itinerario', 'ITINERARIO']);

            // Validar itinerário
            if (empty($itinerario)) {
                $this->updateProgress('warning', "Linha {$this->processedRows}: Campo 'ITINERÁRIO' está vazio. Pulando linha...");
                return null;
            }

            // Processar itinerário - separar por "-" (mesma lógica do SheetTripsImport)
            $locationParts = array_map('trim', explode('-', $itinerario));
            $locationParts = array_filter($locationParts); // Remover vazios
            $locationParts = array_values($locationParts); // Reindexar

            if (count($locationParts) === 0) {
                $this->updateProgress('warning', "Linha {$this->processedRows}: Itinerário inválido após processamento.");
                return null;
            }

            // Processar todos os locais do itinerário
            $locationsCreated = [];
            foreach ($locationParts as $locationName) {
                // Normalizar nome
                $normalizedName = $this->normalizeLocationName($locationName);
                if (!$normalizedName) {
                    continue;
                }

                // Buscar ou criar local (mesma lógica do SheetTripsImport)
                $location = $this->findOrCreateLocationByName($normalizedName);
                
                if ($location) {
                    // Contar apenas locais únicos criados nesta linha
                    $locationId = $location->id;
                    if (!in_array($locationId, $locationsCreated)) {
                        $locationsCreated[] = $locationId;
                        $this->rowsImported++;
                    }
                }
            }

            // Atualizar progresso a cada 10 linhas
            if ($this->processedRows % 10 === 0) {
                $this->updateProgress('row', "Linha {$this->processedRows} processada. Locais encontrados: " . count($locationsCreated));
            }

            // Retornar null pois não estamos criando um modelo específico, apenas processando locais
            return null;
        } catch (\Exception $e) {
            // Log de erro detalhado
            $this->updateProgress('error', "Erro na linha {$this->processedRows}: " . $e->getMessage() . " | Arquivo: " . basename($e->getFile()) . " | Linha: " . $e->getLine());
            
            // Se for nas primeiras linhas, relançar para ver o erro completo
            if ($this->processedRows <= 5) {
                throw $e;
            }
            
            // Caso contrário, apenas pular a linha
            return null;
        }
    }

    public function getRowsImported(): int
    {
        return $this->rowsImported;
    }

    protected function updateProgress($type, $message)
    {
        if (!$this->importId) {
            return;
        }

        $progress = Cache::get("import_progress_{$this->importId}", [
            'status' => 'processing',
            'progress' => 0,
            'total' => 0,
            'processed' => 0,
            'logs' => [],
            'current_sheet' => '',
            'started_at' => now()->toDateTimeString(),
        ]);

        $progress['processed'] = $this->processedRows;
        $progress['current_sheet'] = $this->sheetName ?? '';
        
        // Calcular progresso baseado em linhas processadas
        $minProgress = 5;
        $estimatedProgress = min(95, max($minProgress, ($this->processedRows / 100) * 10));
        $progress['progress'] = $estimatedProgress;
        
        if (!isset($progress['logs'])) {
            $progress['logs'] = [];
        }

        $progress['logs'][] = [
            'time' => now()->format('H:i:s'),
            'type' => $type,
            'message' => $message,
        ];

        // Manter apenas os últimos 100 logs
        if (count($progress['logs']) > 100) {
            $progress['logs'] = array_slice($progress['logs'], -100);
        }

        Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function(BeforeImport $event) {
                \Log::info("BeforeImport disparado para aba: {$this->sheetName}");
                $this->updateProgress('info', "Iniciando importação da aba: {$this->sheetName}");
            },
            AfterImport::class => function(AfterImport $event) {
                \Log::info("AfterImport disparado para aba: {$this->sheetName}. Processadas: {$this->processedRows}, Importadas: {$this->rowsImported}");
                
                $progress = Cache::get("import_progress_{$this->importId}", []);
                
                if ($this->rowsImported === 0) {
                    $progress['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'warning',
                        'message' => "Aba '{$this->sheetName}' processada mas nenhuma linha válida foi encontrada. Linhas processadas: {$this->processedRows}",
                    ];
                } else {
                    $progress['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'success',
                        'message' => "Aba '{$this->sheetName}' concluída! {$this->rowsImported} local(is) importado(s) de {$this->processedRows} linha(s) processada(s).",
                    ];
                }
                
                Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));
            },
        ];
    }
}

