<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KMImport;
use App\Imports\LocationsImport;
use App\Jobs\ProcessImportJob;
use App\Jobs\ProcessLocationsImportJob;
use App\Models\ImportLog;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Exports\TripsExport;
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

            // SEMPRE processar síncrono por padrão (garante que funcione sem worker)
            // Para processar assíncrono, você precisa:
            // 1. Ter um worker rodando: php artisan queue:work
            // 2. Passar ?async=1 na URL da requisição
            $queueConnection = env('QUEUE_CONNECTION', 'sync');
            
            // Por padrão, sempre processar síncrono para garantir funcionamento
            // Só processa assíncrono se explicitamente solicitado via ?async=1
            $processSync = !$request->has('async');
            
            $progress = Cache::get("import_progress_{$importId}", []);
            $progress['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'info',
                'message' => "Configuração da fila: {$queueConnection} | Processamento: " . ($processSync ? 'Síncrono' : 'Assíncrono'),
            ];
            Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
            
            if ($processSync) {
                // Processar imediatamente (síncrono) - útil para debug
                $progress = Cache::get("import_progress_{$importId}", []);
                $progress['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'info',
                    'message' => 'Processando importação de forma síncrona...',
                ];
                Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
                
                try {
                    $progress = Cache::get("import_progress_{$importId}", []);
                    $progress['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'info',
                        'message' => 'Criando instância do Job de processamento...',
                    ];
                    Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
                    
                    $job = new ProcessImportJob(
                        $filePath,
                        $file->getClientOriginalName(),
                        $request->year,
                        $request->vehicle_id,
                        $importId,
                        $sheetNames,
                        $userId
                    );
                    
                    $progress = Cache::get("import_progress_{$importId}", []);
                    $progress['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'info',
                        'message' => 'Job criado. Iniciando processamento...',
                    ];
                    Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
                    
                    $job->handle();
                    
                    $progress = Cache::get("import_progress_{$importId}", []);
                    $progress['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'info',
                        'message' => 'Job concluído com sucesso.',
                    ];
                    Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
                } catch (\Exception $e) {
                    \Log::error('Erro ao processar importação síncrona', [
                        'import_id' => $importId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    
                    $progress = Cache::get("import_progress_{$importId}", []);
                    $progress['status'] = 'error';
                    $progress['error'] = $e->getMessage();
                    $progress['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'error',
                        'message' => 'Erro na importação: ' . $e->getMessage() . ' | Linha: ' . $e->getLine() . ' | Arquivo: ' . basename($e->getFile()),
                    ];
                    Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
                }
            } else {
                // Disparar Job para processar importação em background
                $progress = Cache::get("import_progress_{$importId}", []);
                $progress['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'info',
                    'message' => 'Enfileirando Job para processamento assíncrono...',
                ];
                Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
                
                ProcessImportJob::dispatch(
                    $filePath,
                    $file->getClientOriginalName(),
                    $request->year,
                    $request->vehicle_id,
                    $importId,
                    $sheetNames,
                    $userId
                );
                
                $progress = Cache::get("import_progress_{$importId}", []);
                $progress['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'info',
                    'message' => 'Job enfileirado. Aguardando processamento...',
                ];
                Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
            }

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

    public function export(Request $request)
    {
        try {
            $request->validate([
                'year' => 'required|integer|min:2000|max:2100',
                'vehicle_id' => 'required|exists:vehicles,id',
            ]);

            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            $year = $request->year;

            // Verificar se há viagens para exportar
            $tripsCount = Trip::where('vehicle_id', $request->vehicle_id)
                ->whereYear('date', $year)
                ->count();

            if ($tripsCount === 0) {
                return redirect()->route('settings.index', ['activeTab' => 'import'])
                    ->with('error', "Nenhuma viagem encontrada para o veículo {$vehicle->name} no ano {$year}.");
            }

            // Nome do arquivo: baseado no veículo e ano (mesmo formato da importação)
            $vehicleName = \Illuminate\Support\Str::slug($vehicle->name);
            $filename = "CKM - {$vehicleName} - {$year}";

            // Exportar usando a classe TripsExport
            return Excel::download(
                new TripsExport($request->vehicle_id, $year),
                $filename . '.xlsx'
            );
        } catch (\Exception $e) {
            return redirect()->route('settings.index', ['activeTab' => 'import'])
                ->with('error', 'Erro ao exportar planilha: ' . $e->getMessage());
        }
    }

    /**
     * Importa apenas locais de uma planilha
     */
    public function importLocations(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        $importId = uniqid('import_locations_', true);

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
                'user_id' => $userId,
                'import_type' => 'locations', // Identificar tipo de importação
                'logs' => [
                    [
                        'time' => now()->format('H:i:s'),
                        'type' => 'info',
                        'message' => 'Iniciando importação de locais do arquivo: ' . $file->getClientOriginalName(),
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
                'total_sheets' => count($sheetNames),
                'sheet_names' => $sheetNames,
            ], now()->addHours(1));

            // Processar síncrono por padrão
            $queueConnection = env('QUEUE_CONNECTION', 'sync');
            $processSync = !$request->has('async');
            
            $progress = Cache::get("import_progress_{$importId}", []);
            $progress['logs'][] = [
                'time' => now()->format('H:i:s'),
                'type' => 'info',
                'message' => "Configuração da fila: {$queueConnection} | Processamento: " . ($processSync ? 'Síncrono' : 'Assíncrono'),
            ];
            Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
            
            if ($processSync) {
                // Processar imediatamente (síncrono)
                $progress = Cache::get("import_progress_{$importId}", []);
                $progress['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'info',
                    'message' => 'Processando importação de locais de forma síncrona...',
                ];
                Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
                
                try {
                    $progress = Cache::get("import_progress_{$importId}", []);
                    $progress['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'info',
                        'message' => 'Criando instância do Job de processamento...',
                    ];
                    Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
                    
                    $job = new ProcessLocationsImportJob(
                        $filePath,
                        $file->getClientOriginalName(),
                        $importId,
                        $sheetNames,
                        $userId
                    );
                    
                    $progress = Cache::get("import_progress_{$importId}", []);
                    $progress['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'info',
                        'message' => 'Job criado. Iniciando processamento...',
                    ];
                    Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
                    
                    $job->handle();
                    
                    $progress = Cache::get("import_progress_{$importId}", []);
                    $progress['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'info',
                        'message' => 'Job concluído com sucesso.',
                    ];
                    Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
                } catch (\Exception $e) {
                    \Log::error('Erro ao processar importação de locais síncrona', [
                        'import_id' => $importId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    
                    $progress = Cache::get("import_progress_{$importId}", []);
                    $progress['status'] = 'error';
                    $progress['error'] = $e->getMessage();
                    $progress['logs'][] = [
                        'time' => now()->format('H:i:s'),
                        'type' => 'error',
                        'message' => 'Erro na importação: ' . $e->getMessage() . ' | Linha: ' . $e->getLine() . ' | Arquivo: ' . basename($e->getFile()),
                    ];
                    Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
                }
            } else {
                // Disparar Job para processar importação em background
                $progress = Cache::get("import_progress_{$importId}", []);
                $progress['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'info',
                    'message' => 'Enfileirando Job para processamento assíncrono...',
                ];
                Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
                
                ProcessLocationsImportJob::dispatch(
                    $filePath,
                    $file->getClientOriginalName(),
                    $importId,
                    $sheetNames,
                    $userId
                );
                
                $progress = Cache::get("import_progress_{$importId}", []);
                $progress['logs'][] = [
                    'time' => now()->format('H:i:s'),
                    'type' => 'info',
                    'message' => 'Job enfileirado. Aguardando processamento...',
                ];
                Cache::put("import_progress_{$importId}", $progress, now()->addHours(1));
            }

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
}

