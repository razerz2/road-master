<?php

namespace App\Helpers;

class EnvHelper
{
    /**
     * Atualiza uma variável no arquivo .env
     * 
     * @param string $key
     * @param string $value
     * @return bool
     */
    public static function updateEnv($key, $value)
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            return false;
        }

        $envContent = file_get_contents($envPath);
        
        // Se o valor estiver vazio, remover a variável
        if (empty($value) && $value !== '0') {
            $pattern = "/^{$key}=.*$/m";
            $envContent = preg_replace($pattern, '', $envContent);
            // Remover linhas vazias duplicadas
            $envContent = preg_replace("/\n\n+/", "\n\n", $envContent);
        } else {
            // Escapar caracteres especiais no valor
            $escapedValue = self::escapeEnvValue($value);
            
            // Padrão para encontrar a linha (com ou sem aspas)
            $pattern = "/^{$key}=.*$/m";
            
            // Verificar se a variável já existe
            if (preg_match($pattern, $envContent)) {
                // Substituir a linha existente
                $replacement = "{$key}={$escapedValue}";
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                // Adicionar nova variável no final do arquivo
                $envContent .= "\n{$key}={$escapedValue}";
            }
        }
        
        return file_put_contents($envPath, $envContent) !== false;
    }

    /**
     * Escapa o valor para o formato .env
     * 
     * @param string $value
     * @return string
     */
    private static function escapeEnvValue($value)
    {
        // Se contém espaços ou caracteres especiais, usar aspas
        if (preg_match('/[\s#="\']/', $value)) {
            // Escapar aspas duplas dentro do valor
            $value = str_replace('"', '\"', $value);
            return '"' . $value . '"';
        }
        
        return $value;
    }

    /**
     * Atualiza múltiplas variáveis de uma vez
     * 
     * @param array $variables
     * @return bool
     */
    public static function updateMultipleEnv(array $variables)
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            return false;
        }

        $envContent = file_get_contents($envPath);
        
        foreach ($variables as $key => $value) {
            // Se o valor estiver vazio, remover a variável
            if (empty($value) && $value !== '0') {
                $pattern = "/^{$key}=.*$/m";
                $envContent = preg_replace($pattern, '', $envContent);
            } else {
                $escapedValue = self::escapeEnvValue($value);
                $pattern = "/^{$key}=.*$/m";
                
                if (preg_match($pattern, $envContent)) {
                    $replacement = "{$key}={$escapedValue}";
                    $envContent = preg_replace($pattern, $replacement, $envContent);
                } else {
                    $envContent .= "\n{$key}={$escapedValue}";
                }
            }
        }
        
        // Remover linhas vazias duplicadas
        $envContent = preg_replace("/\n\n+/", "\n\n", $envContent);
        
        return file_put_contents($envPath, $envContent) !== false;
    }
}

