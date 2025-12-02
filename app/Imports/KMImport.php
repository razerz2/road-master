<?php

namespace App\Imports;

// Removido WithMultipleSheets - agora processamos diretamente com SheetTripsImport
// Como há apenas uma aba, não precisamos de múltiplas abas

class KMImport
{
    protected $year;
    protected $vehicleId;
    protected $importId;
    protected $sheetNames;
    protected $userId;

    public function __construct($year, $vehicleId, $importId = null, $sheetNames = [], $userId = null)
    {
        $this->year = $year;
        $this->vehicleId = $vehicleId;
        $this->importId = $importId ?? uniqid('import_', true);
        $this->sheetNames = $sheetNames;
        // Garantir que userId seja um inteiro e não null
        $this->userId = $userId ? (int) $userId : (auth()->id() ? (int) auth()->id() : null);
        
        if (!$this->userId) {
            throw new \Exception('ID do usuário não está disponível. Não é possível processar a importação.');
        }
    }

    public function getImportId(): string
    {
        return $this->importId;
    }
    
    /**
     * Retorna a instância de SheetTripsImport para processar a primeira aba
     */
    public function getSheetImport()
    {
        // Garantir que userId esteja disponível
        if (!$this->userId) {
            // Tentar obter do cache
            $progress = \Illuminate\Support\Facades\Cache::get("import_progress_{$this->importId}", []);
            $this->userId = $progress['user_id'] ?? null;
        }
        
        if (!$this->userId) {
            throw new \Exception('ID do usuário não está disponível no KMImport. importId=' . $this->importId);
        }
        
        // NOVO FORMATO: Processar apenas a primeira aba (única aba com todos os dados)
        $firstSheetName = !empty($this->sheetNames) ? $this->sheetNames[0] : 'Plan1';
        return new SheetTripsImport($this->year, $this->vehicleId, $this->importId, $firstSheetName, $this->userId);
    }
}

