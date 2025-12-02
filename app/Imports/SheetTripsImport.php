<?php

namespace App\Imports;

use App\Models\Trip;
use App\Models\TripStop;
use App\Models\Fueling;
use App\Models\User;
use App\Models\Location;
use App\Models\Vehicle;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SheetTripsImport implements ToModel, WithChunkReading, WithEvents, SkipsEmptyRows, WithStartRow
{
    protected $year;
    protected $vehicleId;
    protected $rowsImported = 0;
    protected $importId;
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
     * - Trim
     * - Remover espaços duplos
     * - ucwords(strtolower())
     * - Remover acentos
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
        $name = ucwords(strtolower($name));

        // Remover acentos
        $name = Str::ascii($name);

        return $name;
    }

    /**
     * Processa o campo Tipo/Qtde do abastecimento
     * Formato: G-22,20 (Letra-Quantidade)
     * Retorna: ['type' => 'Gasolina', 'liters' => 22.20]
     */
    private function parseFuelTypeAndQuantity($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        // Verificar se contém hífen
        if (strpos($value, '-') === false) {
            return null;
        }

        $parts = explode('-', $value, 2);
        if (count($parts) !== 2) {
            return null;
        }

        $typeLetter = strtoupper(trim($parts[0]));
        $quantity = trim($parts[1]);

        // Converter vírgula para ponto
        $quantity = str_replace(',', '.', $quantity);

        if (!is_numeric($quantity)) {
            return null;
        }

        $liters = floatval($quantity);

        // Mapear letra para tipo de combustível
        $fuelTypeMap = [
            'G' => 'Gasolina',
            'E' => 'Etanol',
            'D' => 'Diesel',
        ];

        if (!isset($fuelTypeMap[$typeLetter])) {
            return null;
        }

        return [
            'type' => $fuelTypeMap[$typeLetter],
            'liters' => $liters,
        ];
    }

    /**
     * Converte horário do Excel para formato H:i:s
     */
    private function convertExcelTime($timeValue)
    {
        if (empty($timeValue) && $timeValue !== '0' && $timeValue !== 0) {
            return null;
        }

        // Se já está em formato de hora (HH:MM:SS ou HH:MM), retornar como está
        if (is_string($timeValue) && preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $timeValue)) {
            $parts = explode(':', $timeValue);
            if (count($parts) === 2) {
                return $timeValue . ':00';
            }
            return $timeValue;
        }

        // Se é número (serial do Excel), converter
        if (is_numeric($timeValue)) {
            try {
                if ($timeValue >= 0 && $timeValue < 1) {
                    // É um horário puro (fração de dia)
                    $totalHours = $timeValue * 24;
                    $hours = floor($totalHours);
                    $fractionalHours = $totalHours - $hours;
                    $totalMinutes = $fractionalHours * 60;
                    $minutes = floor($totalMinutes);
                    $fractionalMinutes = $totalMinutes - $minutes;
                    $seconds = round($fractionalMinutes * 60);
                    
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
                    // Pode ser data+hora combinada
                    $phpDate = ExcelDate::excelToDateTimeObject($timeValue);
                    return $phpDate->format('H:i:s');
                }
            } catch (\Exception $e) {
                return null;
            }
        }

        return $timeValue;
    }

    /**
     * Lê coluna por índice ou nome
     */
    private function getColumnValue($row, $index, $possibleNames = [])
    {
        // Tentar por índice numérico primeiro
        if (isset($row[$index])) {
            $value = $row[$index];
            // Não considerar 0 ou '0' como vazio
            if ($value !== null && $value !== '' && trim((string)$value) !== '') {
                return $value;
            }
        }
        // Tentar por nomes possíveis
        foreach ($possibleNames as $name) {
            if (isset($row[$name])) {
                $value = $row[$name];
                // Não considerar 0 ou '0' como vazio
                if ($value !== null && $value !== '' && trim((string)$value) !== '') {
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * Converte data do Excel para formato Y-m-d
     */
    private function convertExcelDate($dateValue)
    {
        if (empty($dateValue)) {
            return null;
        }

        // Se é número serial do Excel
        if (is_numeric($dateValue) && $dateValue > 1000) {
            try {
                $phpDate = ExcelDate::excelToDateTimeObject($dateValue);
                return Carbon::instance($phpDate)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Tentar parsear como string de data (DD/MM/YYYY)
        if (is_string($dateValue) && preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateValue, $matches)) {
            $day = intval($matches[1]);
            $month = intval($matches[2]);
            $year = intval($matches[3]);
            
            try {
                return Carbon::create($year, $month, $day)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
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

            // Ler colunas A-J (índices 0-9)
            // ITINERÁRIO;DATA;HORÁRIO SAÍDA;KM SAÍDA;HORÁRIO CHEGADA;KM CHEGADA;KM RODADOS;Tipo/Qtde;Valor;CONDUTOR
            $itinerario = $this->getColumnValue($row, 0, ['ITINERÁRIO', 'itinerario', 'Itinerario', 'ITINERARIO']);              // Coluna A
            $data = $this->getColumnValue($row, 1, ['DATA', 'data', 'Data']);                                                    // Coluna B
            $departureTime = $this->getColumnValue($row, 2, ['HORÁRIO SAÍDA', 'HORARIO SAIDA', 'horario_saida']);                // Coluna C
            $odometerStartValue = $this->getColumnValue($row, 3, ['KM SAÍDA', 'KM SAIDA', 'km_saida']);                          // Coluna D
            $returnTime = $this->getColumnValue($row, 4, ['HORÁRIO CHEGADA', 'HORARIO CHEGADA', 'horario_chegada']);           // Coluna E
            $odometerEndValue = $this->getColumnValue($row, 5, ['KM CHEGADA', 'KM CHEGADA', 'km_chegada']);                     // Coluna F
            // Coluna G: KM RODADOS - não precisa, calculamos automaticamente
            $fuelTypeQtde = $this->getColumnValue($row, 7, ['Tipo/Qtde', 'TIPO/QTDE', 'tipo_qtde', 'Tipo_Qtde']);               // Coluna H
            $fuelValue = $this->getColumnValue($row, 8, ['Valor', 'VALOR', 'valor']);                                            // Coluna I
            $condutor = $this->getColumnValue($row, 9, ['CONDUTOR', 'condutor', 'Condutor']);                                   // Coluna J

            // Validar dados básicos
            if (empty($data)) {
                $this->updateProgress('warning', "Linha {$this->processedRows}: Campo 'Data' está vazio. Pulando linha...");
                return null;
            }

            if (empty($itinerario)) {
                $this->updateProgress('warning', "Linha {$this->processedRows}: Campo 'Itinerário' está vazio. Pulando linha...");
                return null;
            }

            if (empty($condutor)) {
                $this->updateProgress('warning', "Linha {$this->processedRows}: Campo 'Condutor' está vazio. Pulando linha...");
                return null;
            }

            // Converter data
            $dateString = $this->convertExcelDate($data);
            if (!$dateString) {
                $this->updateProgress('error', "Linha {$this->processedRows}: Erro ao converter data '{$data}'. Pulando linha...");
                return null;
            }

            // Validar condutor (motorista)
            $driver = User::where('name', $condutor)->first();
            if (!$driver) {
                $this->updateProgress('error', "Linha {$this->processedRows}: Motorista '{$condutor}' não encontrado. Pulando linha...");
                return null;
            }

            // Processar itinerário - separar por "-"
            $locationParts = array_map('trim', explode('-', $itinerario));
            $locationParts = array_filter($locationParts); // Remover vazios
            $locationParts = array_values($locationParts); // Reindexar

            if (count($locationParts) === 0) {
                $this->updateProgress('warning', "Linha {$this->processedRows}: Itinerário inválido após processamento.");
                return null;
            }

            // Processar locais
            $origin = null;
            $destination = null;
            $intermediateLocations = [];

            if (count($locationParts) === 1) {
                // Se itinerário tiver apenas 1 item → origem = destino
                $locationName = $this->normalizeLocationName($locationParts[0]);
                if (!$locationName) {
                    $this->updateProgress('warning', "Linha {$this->processedRows}: Nome de local inválido após normalização.");
                    return null;
                }
                $origin = Location::firstOrCreate(['name' => $locationName]);
                $destination = $origin;
            } else {
                // Primeiro item → origin_location_id
                $originName = $this->normalizeLocationName($locationParts[0]);
                if (!$originName) {
                    $this->updateProgress('warning', "Linha {$this->processedRows}: Nome de origem inválido após normalização.");
                    return null;
                }
                $origin = Location::firstOrCreate(['name' => $originName]);

                // Último item → destination_location_id
                $destinationName = $this->normalizeLocationName(end($locationParts));
                if (!$destinationName) {
                    $this->updateProgress('warning', "Linha {$this->processedRows}: Nome de destino inválido após normalização.");
                    return null;
                }
                $destination = Location::firstOrCreate(['name' => $destinationName]);

                // Itens intermediários → criar registros em trip_stops
                if (count($locationParts) > 2) {
                    // Do segundo até o penúltimo
                    for ($i = 1; $i < count($locationParts) - 1; $i++) {
                        $locationName = $this->normalizeLocationName($locationParts[$i]);
                        if ($locationName) {
                            $intermediateLocations[] = Location::firstOrCreate(['name' => $locationName]);
                        }
                    }
                }
            }

            // Validar KM
            $odometerStart = $odometerStartValue ? intval($odometerStartValue) : 0;
            $odometerEnd = $odometerEndValue ? intval($odometerEndValue) : 0;

            // Se KM CHEGADA < KM SAÍDA → registrar erro e pular
            if ($odometerEnd < $odometerStart) {
                $this->updateProgress('error', "Linha {$this->processedRows}: KM Chegada ({$odometerEnd}) menor que KM Saída ({$odometerStart}). Pulando linha...");
                return null;
            }

            if ($odometerStart <= 0 || $odometerEnd <= 0) {
                $this->updateProgress('warning', "Linha {$this->processedRows}: KM inválido. Saída: {$odometerStart}, Chegada: {$odometerEnd}");
                return null;
            }

            // Converter horários
            $departureTimeFormatted = $this->convertExcelTime($departureTime);
            $returnTimeFormatted = $this->convertExcelTime($returnTime);

            // Obter userId
            $progress = Cache::get("import_progress_{$this->importId}", []);
            $userId = $progress['user_id'] ?? $this->userId ?? null;
            
            if (!$userId || $userId === null || $userId === 0) {
                $errorMsg = 'ID do usuário não está disponível. userId=' . var_export($this->userId, true) . ', cache_user_id=' . var_export($progress['user_id'] ?? null, true);
                $this->updateProgress('error', $errorMsg);
                throw new \Exception($errorMsg);
            }

            $userId = (int) $userId;

            // Criar Trip
            $tripId = DB::table('trips')->insertGetId([
                'vehicle_id' => $this->vehicleId,
                'driver_id' => $driver->id,
                'date' => $dateString,
                'origin_location_id' => $origin->id,
                'destination_location_id' => $destination->id,
                'departure_time' => $departureTimeFormatted,
                'return_time' => $returnTimeFormatted,
                'odometer_start' => $odometerStart,
                'odometer_end' => $odometerEnd,
                'km_total' => $odometerEnd - $odometerStart,
                'return_to_origin' => false,
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Criar paradas intermediárias (trip_stops)
            if (count($intermediateLocations) > 0) {
                foreach ($intermediateLocations as $index => $location) {
                    TripStop::create([
                        'trip_id' => $tripId,
                        'location_id' => $location->id,
                        'sequence' => $index + 1, // sequence começa em 1
                    ]);
                }
            }

            // Processar abastecimento se houver dados em Tipo/Qtde e Valor
            if (!empty($fuelTypeQtde) && !empty($fuelValue)) {
                $fuelData = $this->parseFuelTypeAndQuantity($fuelTypeQtde);
                
                if ($fuelData) {
                    $liters = $fuelData['liters'];
                    $fuelType = $fuelData['type'];
                    
                    // Converter valor (pode ter vírgula)
                    $totalAmount = str_replace(',', '.', $fuelValue);
                    $totalAmount = floatval($totalAmount);
                    
                    if ($liters > 0 && $totalAmount > 0) {
                        $pricePerLiter = $totalAmount / $liters;
                        
                        // Usar HORÁRIO CHEGADA se existir, senão usar HORÁRIO SAÍDA, senão usar 12:00:00
                        $fuelDateTime = $returnTimeFormatted ?: ($departureTimeFormatted ?: '12:00:00');
                        $fuelOdometer = $odometerEnd; // Usar KM CHEGADA
                        
                        // Combinar DATA + HORÁRIO para date_time
                        try {
                            $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dateString . ' ' . $fuelDateTime);
                        } catch (\Exception $e) {
                            // Se falhar, usar apenas a data com horário padrão
                            $dateTime = Carbon::createFromFormat('Y-m-d', $dateString)->setTime(12, 0, 0);
                        }
                        
                        // Criar registro de abastecimento
                        Fueling::create([
                            'vehicle_id' => $this->vehicleId,
                            'user_id' => $userId,
                            'date_time' => $dateTime,
                            'odometer' => $fuelOdometer,
                            'fuel_type' => $fuelType,
                            'liters' => $liters,
                            'price_per_liter' => $pricePerLiter,
                            'total_amount' => $totalAmount,
                        ]);

                        // Atualizar odômetro do veículo se necessário
                        // Regra obrigatória: se fueling.odometer > vehicle.current_odometer → atualizar veículo
                        $vehicle = Vehicle::find($this->vehicleId);
                        if ($vehicle) {
                            $currentOdometer = $vehicle->current_odometer ?? 0;
                            if ($fuelOdometer > $currentOdometer) {
                                $vehicle->current_odometer = $fuelOdometer;
                                $vehicle->save();
                            }
                        }
                    }
                }
            }

            $this->rowsImported++;
            
            // Atualizar progresso a cada 10 linhas
            if ($this->processedRows % 10 === 0) {
                $this->updateProgress('row', "Linha {$this->processedRows} processada");
            }

            // Retornar o modelo criado
            return Trip::find($tripId);
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
                $this->updateProgress('info', "Configuração: startRow={$this->startRow()}, vehicleId={$this->vehicleId}, year={$this->year}");
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
                        'message' => "Aba '{$this->sheetName}' concluída! {$this->rowsImported} linha(s) importada(s) de {$this->processedRows} linha(s) processada(s).",
                    ];
                }
                
                Cache::put("import_progress_{$this->importId}", $progress, now()->addHours(1));
            },
        ];
    }
}
