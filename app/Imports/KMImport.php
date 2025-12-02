<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KMImport implements WithMultipleSheets
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

    public function sheets(): array
    {
        $sheets = [];
        
        // Garantir que userId esteja disponível
        if (!$this->userId) {
            // Tentar obter do cache
            $progress = \Illuminate\Support\Facades\Cache::get("import_progress_{$this->importId}", []);
            $this->userId = $progress['user_id'] ?? null;
        }
        
        if (!$this->userId) {
            throw new \Exception('ID do usuário não está disponível no KMImport. importId=' . $this->importId);
        }
        
        foreach ($this->sheetNames as $sheetName) {
            // Verificar se a aba tem nome de mês válido antes de adicionar
            $month = $this->detectMonth($sheetName);
            if ($month) {
                $sheets[$sheetName] = new SheetTripsImport($this->year, $this->vehicleId, $this->importId, $sheetName, $this->userId);
            }
        }
        
        return $sheets;
    }

    private function detectMonth($sheetName)
    {
        $sheetName = strtolower($sheetName);
        $sheetName = \Illuminate\Support\Str::ascii($sheetName);

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
            if (str_contains($sheetName, $key)) {
                return $value;
            }
        }

        return null;
    }
}

