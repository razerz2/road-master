<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KMImport;
use App\Jobs\ProcessImportJob;
use App\Models\ImportLog;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index', [
            'vehicles' => Vehicle::orderBy('name')->get()
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
            'year' => 'required|integer|min:2000|max:2100',
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $file = $request->file('file');
        $importId = uniqid('import_', true);

        try {
            // Salvar arquivo temporariamente
            $filePath = $file->storeAs('imports', $importId . '_' . $file->getClientOriginalName(), 'local');
            
            // Ler os nomes das abas do arquivo
            $tempPath = $file->getRealPath();
            $spreadsheet = IOFactory::load($tempPath);
            $sheetNames = $spreadsheet->getSheetNames();

            // Obter ID do usuário autenticado
            $userId = auth()->id();
            if (!$userId) {
                throw new \Exception('Usuário não autenticado. Faça login novamente.');
            }
            
            // Garantir que userId seja um inteiro
            $userId = (int) $userId;

            // Inicializar progresso
            Cache::put("import_progress_{$importId}", [
                'status' => 'processing',
                'progress' => 0,
                'total' => 0,
                'processed' => 0,
                'user_id' => $userId, // Armazenar userId no cache como backup
                'logs' => [
                    [
                        'time' => now()->format('H:i:s'),
                        'type' => 'info',
                        'message' => 'Iniciando importação do arquivo: ' . $file->getClientOriginalName(),
                    ],
                    [
                        'time' => now()->format('H:i:s'),
                        'type' => 'info',
                        'message' => 'Arquivo carregado. Encontradas ' . count($sheetNames) . ' aba(s): ' . implode(', ', $sheetNames),
                    ],
                ],
                'current_sheet' => '',
                'started_at' => now()->toDateTimeString(),
                'file_name' => $file->getClientOriginalName(),
                'year' => $request->year,
                'vehicle_id' => $request->vehicle_id,
                'total_sheets' => count($sheetNames),
                'sheet_names' => $sheetNames,
            ], now()->addHours(1));

            // Disparar Job para processar importação em background
            ProcessImportJob::dispatch(
                $filePath,
                $file->getClientOriginalName(),
                $request->year,
                $request->vehicle_id,
                $importId,
                $sheetNames,
                $userId
            );

            // Redirecionar imediatamente para a página de progresso
            return redirect()->route('import.progress', ['id' => $importId]);
        } catch (\Exception $e) {
            $progress = Cache::get("import_progress_{$importId}", []);
            $progress['status'] = 'error';
            $progress['error'] = $e->getMessage();
            $progress['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'error',
                'message' => 'Erro ao preparar importação: ' . $e->getMessage(),
            ];
            Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));

            return redirect()->route('import.progress', ['id' => $importId])
                ->with('error', 'Erro ao importar arquivo: ' . $e->getMessage());
        }
    }

    public function progress(Request $request, $id)
    {
        $progress = Cache::get("import_progress_{$id}", null);

        if (!$progress) {
            return redirect()->route('import.index')
                ->with('error', 'Importação não encontrada ou expirada.');
        }

        return view('import.progress', [
            'importId' => $id,
            'progress' => $progress,
        ]);
    }

    public function status(Request $request, $id)
    {
        $progress = Cache::get("import_progress_{$id}", null);

        if (!$progress) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Importação não encontrada ou expirada.',
            ], 404);
        }

        return response()->json($progress);
    }
}

