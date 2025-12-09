<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Location;
use Carbon\Carbon;

class DeleteImportedLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:delete-imported 
                            {--today : Apagar locais criados hoje}
                            {--hours= : Apagar locais criados nas últimas X horas}
                            {--since= : Apagar locais criados após uma data (formato: Y-m-d H:i:s ou Y-m-d)}
                            {--force : Não pedir confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apaga locais criados pela importação incorreta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Location::query();

        // Determinar o critério de data
        if ($this->option('today')) {
            $since = Carbon::today();
            $this->info('🗑️  Apagando locais criados HOJE...');
        } elseif ($hours = $this->option('hours')) {
            $since = Carbon::now()->subHours((int)$hours);
            $this->info("🗑️  Apagando locais criados nas últimas {$hours} horas...");
        } elseif ($sinceDate = $this->option('since')) {
            try {
                $since = Carbon::parse($sinceDate);
                $this->info("🗑️  Apagando locais criados após {$since->format('d/m/Y H:i:s')}...");
            } catch (\Exception $e) {
                $this->error("❌ Data inválida: {$sinceDate}");
                $this->error("Use o formato: Y-m-d H:i:s ou Y-m-d");
                return 1;
            }
        } else {
            // Por padrão, apagar locais criados hoje
            $since = Carbon::today();
            $this->info('🗑️  Apagando locais criados HOJE (padrão)...');
            $this->warn('💡 Dica: Use --hours=X para apagar locais das últimas X horas');
            $this->warn('💡 Dica: Use --since="Y-m-d H:i:s" para apagar locais após uma data específica');
        }

        // Contar locais que serão apagados
        $count = $query->where('created_at', '>=', $since)->count();

        if ($count === 0) {
            $this->info('✅ Nenhum local encontrado para apagar.');
            return 0;
        }

        // Mostrar informações
        $this->info("📊 Total de locais que serão apagados: {$count}");
        
        // Listar alguns exemplos
        $examples = $query->where('created_at', '>=', $since)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'created_at']);

        if ($examples->count() > 0) {
            $this->newLine();
            $this->info('📋 Exemplos de locais que serão apagados:');
            $this->table(
                ['ID', 'Nome', 'Criado em'],
                $examples->map(function ($location) {
                    return [
                        $location->id,
                        $location->name,
                        $location->created_at->format('d/m/Y H:i:s')
                    ];
                })->toArray()
            );
        }

        // Pedir confirmação
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  Tem certeza que deseja apagar estes locais? Esta ação não pode ser desfeita!')) {
                $this->info('❌ Operação cancelada.');
                return 0;
            }
        }

        // Apagar locais
        $deleted = $query->where('created_at', '>=', $since)->delete();

        $this->newLine();
        $this->info("✅ {$deleted} local(is) apagado(s) com sucesso!");

        return 0;
    }
}
