<?php

namespace App\Imports;

use App\Models\Trip;
use App\Models\User;
use App\Models\Location;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Cache;

class SheetTripsImport implements ToModel, WithChunkReading, WithEvents, SkipsEmptyRows, WithValidation, WithStartRow
{
    protected $year;
    protected $vehicleId;
    protected $rowsImported = 0;
    protected $importId;
    protected $currentSheet = '';
    protected $totalRows = 0;
    protected $processedRows = 0;
    protected $sheetName;
    protected $stopProcessing = false;
    protected $userId;

    public function __construct($year, $vehicleId, $importId = null, $sheetName = null, $userId = null)
    {
        $this->year = $year;
        $this->vehicleId = $vehicleId;
        $this->importId = $importId ?? uniqid('import_', true);
        $this->sheetName = $sheetName;
        $this->currentSheet = $sheetName ?? '';
        
        // Tentar obter userId de várias fontes
        if ($userId) {
            $this->userId = (int) $userId;
        } elseif (auth()->id()) {
            $this->userId = (int) auth()->id();
        } elseif ($importId) {
            // Tentar obter do cache como último recurso
            $progress = Cache::get("import_progress_{$importId}", []);
            $this->userId = isset($progress['user_id']) ? (int) $progress['user_id'] : null;
        } else {
            $this->userId = null;
        }
        
        // Se ainda não tiver userId, não lançar exceção aqui - vamos tentar novamente no model()
        // porque pode ser que o Excel esteja criando instâncias de forma diferente
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function startRow(): int
    {
        return 9; // Dados começam na linha 9
    }

    public function headingRow(): int
    {
        return null; // Não usar cabeçalho, ler por posição de coluna (A=0, B=1, C=2, etc.)
    }

    public function getImportId(): string
    {
        return $this->importId;
    }

    private function isRowEmpty($row)
    {
        // Verificar se todos os valores da linha estão vazios
        foreach ($row as $value) {
            if (!empty($value) && trim($value) !== '') {
                return false;
            }
        }
        return true;
    }

    private function convertExcelTime($timeValue)
    {
        if (empty($timeValue) && $timeValue !== '0' && $timeValue !== 0) {
            return null;
        }

        // Se já está em formato de hora (HH:MM:SS ou HH:MM), retornar como está
        if (is_string($timeValue) && preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $timeValue)) {
            // Garantir formato HH:MM:SS
            $parts = explode(':', $timeValue);
            if (count($parts) === 2) {
                return $timeValue . ':00';
            }
            return $timeValue;
        }

        // Se é número (serial do Excel), converter
        if (is_numeric($timeValue)) {
            try {
                // No Excel, horário é uma fração de dia (0.0 a 0.999...)
                // 0.0 = 00:00:00, 0.5 = 12:00:00, 0.75 = 18:00:00
                // 0.35416666666667 = 08:30:00, 0.72916666666667 = 17:30:00
                if ($timeValue >= 0 && $timeValue < 1) {
                    // É um horário puro (fração de dia)
                    // Converter fração de dia para horas, minutos e segundos
                    $totalHours = $timeValue * 24;
                    $hours = floor($totalHours);
                    $fractionalHours = $totalHours - $hours;
                    $totalMinutes = $fractionalHours * 60;
                    $minutes = floor($totalMinutes);
                    $fractionalMinutes = $totalMinutes - $minutes;
                    $seconds = round($fractionalMinutes * 60);
                    
                    // Ajustar se segundos chegarem a 60
                    if ($seconds >= 60) {
                        $seconds = 0;
                        $minutes++;
                    }
                    if ($minutes >= 60) {
                        $minutes = 0;
                        $hours++;
                    }
                    
                    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                } else {
                    // Pode ser data+hora combinada (número serial completo)
                    $phpDate = ExcelDate::excelToDateTimeObject($timeValue);
                    return $phpDate->format('H:i:s');
                }
            } catch (\Exception $e) {
                return null;
            }
        }

        return $timeValue;
    }

    private function getColumnValue($row, $possibleNames)
    {
        foreach ($possibleNames as $name) {
            // Tentar nome exato
            if (isset($row[$name]) && !empty($row[$name]) && trim($row[$name]) !== '') {
                return $row[$name];
            }
            // Tentar lowercase
            $lowerName = strtolower($name);
            if (isset($row[$lowerName]) && !empty($row[$lowerName]) && trim($row[$lowerName]) !== '') {
                return $row[$lowerName];
            }
            // Tentar com underscore (espaços viram underscore)
            $underscoreName = str_replace(' ', '_', strtolower($name));
            if (isset($row[$underscoreName]) && !empty($row[$underscoreName]) && trim($row[$underscoreName]) !== '') {
                return $row[$underscoreName];
            }
        }
        return null;
    }

    private function detectMonth($sheetName)
    {
        $sheetName = Str::lower($sheetName);
        $sheetName = Str::ascii($sheetName); // Remove acentos

        $months = [
            'janeiro' => 1, 'jan' => 1,
            'fevereiro' => 2, 'fev' => 2,
            'marco' => 3, 'mar' => 3,
            'abril' => 4, 'abr' => 4,
            'maio' => 5, 'mai' => 5,
            'junho' => 6, 'jun' => 6,
            'julho' => 7, 'jul' => 7,
            'agosto' => 8, 'ago' => 8,
            'setembro' => 9, 'set' => 9,
            'outubro' => 10, 'out' => 10,
            'novembro' => 11, 'nov' => 11,
            'dezembro' => 12, 'dez' => 12
        ];

        foreach ($months as $key => $value) {
            if (Str::contains($sheetName, $key)) {
                return $value;
            }
        }

        return null;
    }

    public function rules(): array
    {
        return [
            // Validações básicas - mas vamos fazer validação manual também
        ];
    }

    public function model(array $row)
    {
        // Se já encontrou "TOTAL KM RODADOS", parar processamento
        if ($this->stopProcessing) {
            return null;
        }

        $sheetName = $this->sheetName ?? $this->currentSheet;
        
        // Log da primeira linha processada de cada aba
        if ($this->currentSheet !== $sheetName) {
            $this->currentSheet = $sheetName;
            $this->updateProgress('sheet', "Processando aba: {$sheetName}");
            $this->stopProcessing = false; // Resetar flag ao mudar de aba
        }

        // Verificar se a linha está completamente vazia
        if ($this->isRowEmpty($row)) {
            return null;
        }

        $this->processedRows++;
        
        $month = $this->detectMonth($sheetName);
        if (!$month) {
            // Log quando não detecta o mês (apenas na primeira vez)
            if ($this->processedRows === 1) {
                $this->updateProgress('warning', "Aba '{$sheetName}' não contém nome de mês válido. Pulando...");
            }
            return null;
        }

        // Debug: verificar se as colunas estão corretas (apenas na primeira linha)
        if ($this->processedRows === 1 && $this->currentSheet === $sheetName) {
            $columns = array_keys($row);
            $this->updateProgress('info', "Primeira linha da aba '{$sheetName}'. Colunas encontradas: " . implode(', ', $columns));
            // Se as colunas são numéricas, tentar ler sem heading row
            if (isset($columns[0]) && is_numeric($columns[0])) {
                $this->updateProgress('warning', "Cabeçalhos não detectados! Tentando ler por posição de coluna...");
            }
        }

        // Verificar se as colunas são numéricas (sem cabeçalho)
        $hasNumericKeys = false;
        $firstKey = array_key_first($row);
        if ($firstKey !== null && is_numeric($firstKey)) {
            $hasNumericKeys = true;
        }

        // Mapeamento das colunas conforme a planilha:
        // A (0): ITINERÁRIO
        // B (1): DATA
        // C (2): HORÁRIO SAÍDA
        // D (3): KM SAÍDA
        // E (4): HORÁRIO CHEGADA
        // F (5): KM CHEGADA
        // G (6): KM RODADOS
        // H (7): ABASTECIMENTO Tipo/Qtde
        // I (8): ABASTECIMENTO Valor
        // J (9): CONTUDOR (Motorista)
        
        if ($hasNumericKeys) {
            // Verificar se encontrou "TOTAL KM RODADOS" - parar leitura desta aba
            $itinerario = $row[0] ?? null;
            if (!empty($itinerario) && stripos($itinerario, 'TOTAL KM RODADOS') !== false) {
                $this->stopProcessing = true;
                $this->updateProgress('info', "Encontrado 'TOTAL KM RODADOS' na linha {$this->processedRows}. Finalizando leitura desta aba.");
                return null;
            }

            // Ler por índice de coluna conforme mapeamento
            $itinerario = $row[0] ?? null;              // Coluna A: ITINERÁRIO
            $data = $row[1] ?? null;                    // Coluna B: DATA
            $departureTime = $row[2] ?? null;           // Coluna C: HORÁRIO SAÍDA
            $odometerStartValue = $row[3] ?? null;      // Coluna D: KM SAÍDA
            $returnTime = $row[4] ?? null;              // Coluna E: HORÁRIO CHEGADA
            $odometerEndValue = $row[5] ?? null;        // Coluna F: KM CHEGADA
            // Coluna G (6): KM RODADOS - não precisa, calculamos automaticamente
            // Coluna H (7): ABASTECIMENTO Tipo/Qtde - não usado na importação de percursos
            // Coluna I (8): ABASTECIMENTO Valor - não usado na importação de percursos
            $condutor = $row[9] ?? null;               // Coluna J: CONTUDOR (Condutor)

            // Extrair origem e destino do itinerário
            // Formato: "ORIGEM - DESTINO - ORIGEM" ou "ORIGEM - DESTINO"
            $originName = '';
            $destinationName = '';
            $returnToOrigin = false;
            
            if (!empty($itinerario)) {
                // Limpar espaços e dividir por "-"
                $parts = array_map('trim', explode('-', $itinerario));
                $parts = array_filter($parts); // Remover vazios
                $parts = array_values($parts); // Reindexar
                
                if (count($parts) >= 2) {
                    $originName = $parts[0];
                    $destinationName = $parts[1];
                    
                    // Se tiver 3 partes e a terceira for igual à primeira, significa retorno
                    if (count($parts) >= 3 && strtolower(trim($parts[2])) === strtolower(trim($parts[0]))) {
                        $returnToOrigin = true;
                    }
                } else {
                    // Se não tiver formato esperado, usar o próprio itinerário como destino
                    $originName = 'WPS'; // Assumindo origem padrão
                    $destinationName = trim($itinerario);
                }
            }

            // Debug: mostrar valores da primeira linha
            if ($this->processedRows === 1) {
                $this->updateProgress('info', "Valores da primeira linha: Itinerário={$itinerario}, Data={$data}, Condutor={$condutor}, Origem={$originName}, Destino={$destinationName}, Retorno={$returnToOrigin}, Horário Saída={$departureTime}, Horário Chegada={$returnTime}, KM Saida={$odometerStartValue}, KM Chegada={$odometerEndValue}");
            }
        } else {
            // Ler por nome de coluna (com cabeçalho)
            $itinerario = $this->getColumnValue($row, ['itinerario', 'Itinerario', 'ITINERARIO', 'rota', 'Rota']);
            $data = $this->getColumnValue($row, ['data', 'Data', 'DATA']);
            $condutor = $this->getColumnValue($row, ['motorista', 'Motorista', 'MOTORISTA', 'condutor', 'Condutor', 'contudor', 'Contudor']);
            $originName = $this->getColumnValue($row, ['local_inicio', 'Local Inicio', 'LOCAL_INICIO', 'local inicio', 'origem', 'Origem']);
            $destinationName = $this->getColumnValue($row, ['local_final', 'Local Final', 'LOCAL_FINAL', 'local final', 'destino', 'Destino']);
            $departureTime = $this->getColumnValue($row, ['horario_saida', 'Horario Saida', 'HORARIO_SAIDA', 'horario saida', 'hora_saida', 'Hora Saida']);
            $odometerStartValue = $this->getColumnValue($row, ['km_saida', 'KM Saida', 'KM_SAIDA', 'km saida', 'odometro_saida', 'Odometro Saida']);
            $returnTime = $this->getColumnValue($row, ['horario_chegada', 'Horario Chegada', 'HORARIO_CHEGADA', 'horario chegada', 'hora_chegada', 'Hora Chegada']);
            $odometerEndValue = $this->getColumnValue($row, ['km_chegada', 'KM Chegada', 'KM_CHEGADA', 'km chegada', 'odometro_chegada', 'Odometro Chegada']);
            
            // Extrair origem e destino do itinerário se disponível
            $returnToOrigin = false;
            if (!empty($itinerario)) {
                $parts = array_map('trim', explode('-', $itinerario));
                $parts = array_filter($parts);
                $parts = array_values($parts);
                
                if (count($parts) >= 2) {
                    $originName = $originName ?: $parts[0];
                    $destinationName = $destinationName ?: $parts[1];
                    
                    if (count($parts) >= 3 && strtolower(trim($parts[2])) === strtolower(trim($parts[0]))) {
                        $returnToOrigin = true;
                    }
                }
            }
        }

        // Debug: verificar se dados estão vazios
        if (empty($data)) {
            if ($this->processedRows <= 3) {
                $this->updateProgress('warning', "Linha {$this->processedRows}: Campo 'Data' está vazio. Pulando linha...");
            }
            return null;
        }

        // Converter data do Excel
        // A data pode vir como número serial do Excel (45839, 45870, etc.) ou como string "01/08/2025"
        $date = null;
        $day = null;
        
        // Verificar se é número serial do Excel (número grande, geralmente > 1000)
        if (is_numeric($data) && $data > 1000) {
            try {
                // Converter número serial do Excel para data PHP
                $phpDate = ExcelDate::excelToDateTimeObject($data);
                $date = Carbon::instance($phpDate);
                $day = $date->day;
                
                // Verificar se o mês da data corresponde ao mês da aba
                if ($date->month !== $month) {
                    if ($this->processedRows <= 3) {
                        $this->updateProgress('warning', "Linha {$this->processedRows}: Mês da data ({$date->month}) não corresponde ao mês da aba ({$month}). Pulando...");
                    }
                    return null;
                }
            } catch (\Exception $e) {
                if ($this->processedRows <= 3) {
                    $this->updateProgress('error', "Linha {$this->processedRows}: Erro ao converter data serial '{$data}'. Erro: " . $e->getMessage());
                }
                return null;
            }
        } else {
            // Tentar parsear como string de data (DD/MM/YYYY ou apenas DD)
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $data, $matches)) {
                $day = intval($matches[1]);
                $dataMonth = intval($matches[2]);
                $dataYear = intval($matches[3]);
                
                // Verificar se o mês da data corresponde ao mês da aba
                if ($dataMonth !== $month) {
                    if ($this->processedRows <= 3) {
                        $this->updateProgress('warning', "Linha {$this->processedRows}: Mês da data ({$dataMonth}) não corresponde ao mês da aba ({$month}). Pulando...");
                    }
                    return null;
                }
                
                try {
                    $date = Carbon::create($dataYear, $dataMonth, $day);
                } catch (\Exception $e) {
                    if ($this->processedRows <= 3) {
                        $this->updateProgress('error', "Linha {$this->processedRows}: Erro ao criar data. Ano: {$dataYear}, Mês: {$dataMonth}, Dia: {$day}. Erro: " . $e->getMessage());
                    }
                    return null;
                }
            } else {
                // Tentar extrair apenas o número do dia
                $day = intval(preg_replace('/\D/', '', $data));
                if ($day < 1 || $day > 31) {
                    if ($this->processedRows <= 3) {
                        $this->updateProgress('warning', "Linha {$this->processedRows}: Dia inválido extraído de '{$data}'. Valor: {$day}");
                    }
                    return null;
                }
                
                try {
                    $date = Carbon::create($this->year, $month, $day);
                } catch (\Exception $e) {
                    if ($this->processedRows <= 3) {
                        $this->updateProgress('error', "Linha {$this->processedRows}: Erro ao criar data. Ano: {$this->year}, Mês: {$month}, Dia: {$day}. Erro: " . $e->getMessage());
                    }
                    return null;
                }
            }
        }

        if (empty($condutor)) {
            if ($this->processedRows <= 3) {
                $this->updateProgress('warning', "Linha {$this->processedRows}: Campo 'Condutor' está vazio. Pulando linha...");
            }
            return null;
        }

        // Buscar ou criar condutor, garantindo email único
        $driver = User::where('name', $condutor)->first();
        
        if (!$driver) {
            // Gerar email único baseado no nome
            $baseEmail = Str::slug($condutor).'@import.local';
            $email = $baseEmail;
            $counter = 1;
            
            // Garantir que o email seja único
            while (User::where('email', $email)->exists()) {
                $email = Str::slug($condutor).$counter.'@import.local';
                $counter++;
            }
            
            $driver = User::create([
                'name' => $condutor,
                'email' => $email,
                'password' => bcrypt('123456'),
                'role' => 'condutor',
                'active' => true,
            ]);
        }

        if (empty($originName) || empty($destinationName)) {
            if ($this->processedRows <= 3) {
                $this->updateProgress('warning', "Linha {$this->processedRows}: Origem ou Destino vazios. Origem: '{$originName}', Destino: '{$destinationName}'");
            }
            return null;
        }

        $origin = Location::firstOrCreate(['name' => $originName]);
        $destination = Location::firstOrCreate(['name' => $destinationName]);

        $odometerStart = $odometerStartValue ? intval($odometerStartValue) : 0;
        $odometerEnd = $odometerEndValue ? intval($odometerEndValue) : 0;

        if ($odometerStart <= 0 || $odometerEnd <= 0 || $odometerEnd < $odometerStart) {
            if ($this->processedRows <= 3) {
                $this->updateProgress('warning', "Linha {$this->processedRows}: KM inválido. Saída: {$odometerStart}, Chegada: {$odometerEnd}");
            }
            return null;
        }

        // Converter horários do Excel (se vierem como número serial)
        $departureTimeFormatted = $this->convertExcelTime($departureTime);
        $returnTimeFormatted = $this->convertExcelTime($returnTime);

        $this->rowsImported++;
        
        // Atualizar progresso a cada 10 linhas para não sobrecarregar o cache
        if ($this->processedRows % 10 === 0) {
            $this->updateProgress('row', "Linha {$this->processedRows} processada");
        }

        // SEMPRE obter userId do cache para garantir que está correto
        // O Excel pode criar novas instâncias sem preservar propriedades
        $progress = Cache::get("import_progress_{$this->importId}", []);
        $userId = $progress['user_id'] ?? $this->userId ?? null;
        
        if (!$userId || $userId === null || $userId === 0) {
            $errorMsg = 'ID do usuário não está disponível. userId=' . var_export($this->userId, true) . ', cache_user_id=' . var_export($progress['user_id'] ?? null, true) . ', importId=' . $this->importId;
            $this->updateProgress('error', $errorMsg);
            throw new \Exception($errorMsg);
        }

        // Garantir que seja um inteiro válido
        $userId = (int) $userId;
        
        if ($userId <= 0) {
            $errorMsg = 'ID do usuário inválido: ' . $userId;
            $this->updateProgress('error', $errorMsg);
            throw new \Exception($errorMsg);
        }
        
        // Log de debug (apenas nas primeiras linhas)
        if ($this->processedRows <= 3) {
            $this->updateProgress('info', "DEBUG: Criando trip com userId={$userId}, importId={$this->importId}");
        }

        // Usar DB::insert() diretamente para garantir que created_by seja sempre incluído
        // O Laravel Excel pode usar insertGetId de forma que ignora atributos do modelo
        $tripId = \Illuminate\Support\Facades\DB::table('trips')->insertGetId([
            'vehicle_id' => $this->vehicleId,
            'driver_id' => $driver->id,
            'date' => $date->format('Y-m-d'),
            'departure_time' => $departureTimeFormatted,
            'odometer_start' => $odometerStart,
            'return_time' => $returnTimeFormatted,
            'odometer_end' => $odometerEnd,
            'km_total' => $odometerEnd - $odometerStart,
            'origin_location_id' => $origin->id,
            'destination_location_id' => $destination->id,
            'return_to_origin' => $returnToOrigin,
            'created_by' => $userId, // CRÍTICO: garantir que seja incluído
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Carregar o modelo para retornar
        $trip = Trip::find($tripId);
        
        // Verificar se created_by foi salvo corretamente
        if (!$trip || !$trip->created_by || $trip->created_by !== $userId) {
            $errorMsg = "Erro: created_by não foi salvo corretamente. Esperado: {$userId}, Obtido: " . var_export($trip->created_by ?? null, true);
            $this->updateProgress('error', $errorMsg);
            throw new \Exception($errorMsg);
        }

        // Atualizar odômetro do veículo com o maior valor entre o atual e o KM de chegada
        $vehicle = \App\Models\Vehicle::find($this->vehicleId);
        if ($vehicle) {
            $currentOdometer = $vehicle->current_odometer ?? 0;
            if ($odometerEnd > $currentOdometer) {
                $vehicle->current_odometer = $odometerEnd;
                $vehicle->save();
            }
        }

        return $trip;
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
        $progress['current_sheet'] = $this->currentSheet;
        
        // Calcular progresso baseado em linhas processadas (aproximado)
        // Como não sabemos o total exato, vamos usar um cálculo incremental
        // Progresso mínimo de 5% para indicar que está processando
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
                $this->updateProgress('info', "Iniciando importação da aba: {$this->sheetName}");
            },
            AfterImport::class => function(AfterImport $event) {
                $progress = Cache::get("import_progress_{$this->importId}", []);
                
                if ($this->rowsImported === 0) {
                    $progress['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'warning',
                        'message' => "Aba '{$this->sheetName}' processada mas nenhuma linha válida foi encontrada (aba vazia ou sem dados válidos).",
                    ];
                } else {
                    $progress['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'success',
                        'message' => "Aba '{$this->sheetName}' concluída! {$this->rowsImported} linha(s) importada(s) de {$this->processedRows} linha(s) processada(s).",
                    ];
                }
                
                Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));
            },
        ];
    }
}

