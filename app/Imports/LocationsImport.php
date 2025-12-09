<?php

namespace App\Imports;

class LocationsImport
{
    protected $importId;
    protected $sheetNames;
    protected $userId;

    public function __construct($importId = null, $sheetNames = [], $userId = null)
    {
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
     * Retorna a instância de SheetLocationsImport para processar a primeira aba
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
            throw new \Exception('ID do usuário não está disponível no LocationsImport. importId=' . $this->importId);
        }
        
        // Processar apenas a primeira aba
        $firstSheetName = !empty($this->sheetNames) ? $this->sheetNames[0] : 'Plan1';
        return new SheetLocationsImport($this->importId, $firstSheetName, $this->userId);
    }
}

