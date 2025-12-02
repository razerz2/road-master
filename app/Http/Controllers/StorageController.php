<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    /**
     * Serve arquivos do storage público
     */
    public function serve(Request $request, $path)
    {
        try {
            // Limpar o path de possíveis tentativas de path traversal
            $path = str_replace('..', '', $path);
            $path = ltrim($path, '/');

            // Verificar se o arquivo existe no storage
            if (!Storage::disk('public')->exists($path)) {
                \Log::warning("Arquivo não encontrado no storage: {$path}");
                abort(404, 'Arquivo não encontrado');
            }

            // Obter o caminho completo do arquivo
            $filePath = Storage::disk('public')->path($path);

            // Verificar se o arquivo realmente existe no sistema de arquivos
            if (!file_exists($filePath) || !is_file($filePath)) {
                \Log::warning("Arquivo não existe no sistema de arquivos: {$filePath}");
                abort(404, 'Arquivo não encontrado');
            }

            // Verificar se é legível
            if (!is_readable($filePath)) {
                \Log::error("Arquivo não é legível: {$filePath}");
                abort(403, 'Acesso negado ao arquivo');
            }

            // Determinar o tipo MIME
            $mimeType = mime_content_type($filePath);
            
            // Se não conseguir determinar, usar um padrão baseado na extensão
            if (!$mimeType || $mimeType === 'application/octet-stream') {
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'svg' => 'image/svg+xml',
                    'ico' => 'image/x-icon',
                    'webp' => 'image/webp',
                ];
                $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            }

            // Retornar o arquivo com headers apropriados
            // Usar response()->download() com 'inline' ou response()->make() para evitar problemas de permissão
            return response()->make(file_get_contents($filePath), 200, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=31536000',
                'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
                'Content-Length' => filesize($filePath),
            ]);
        } catch (\Exception $e) {
            \Log::error("Erro ao servir arquivo: {$path}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Erro ao servir arquivo');
        }
    }
}

