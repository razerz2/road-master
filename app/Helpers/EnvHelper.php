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
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $envContent));
        $found = false;
        
        $escapedValue = self::escapeEnvValue($value);
        $newLine = "{$key}={$escapedValue}";

        foreach ($lines as $i => $line) {
            if (strpos(trim($line), "{$key}=") === 0) {
                if (empty($value) && $value !== '0') {
                    unset($lines[$i]);
                } else {
                    $lines[$i] = $newLine;
                }
                $found = true;
                break;
            }
        }

        if (!$found && (!empty($value) || $value === '0')) {
            $lines[] = $newLine;
        }

        return file_put_contents($envPath, implode("\n", $lines)) !== false;
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
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $envContent));
        
        foreach ($variables as $key => $value) {
            $found = false;
            $escapedValue = self::escapeEnvValue($value);
            $newLine = "{$key}={$escapedValue}";

            foreach ($lines as $i => $line) {
                if (strpos(trim($line), "{$key}=") === 0) {
                    if (empty($value) && $value !== '0') {
                        unset($lines[$i]);
                    } else {
                        $lines[$i] = $newLine;
                    }
                    $found = true;
                    break;
                }
            }

            if (!$found && (!empty($value) || $value === '0')) {
                $lines[] = $newLine;
            }
        }
        
        return file_put_contents($envPath, implode("\n", $lines)) !== false;
    }
}

