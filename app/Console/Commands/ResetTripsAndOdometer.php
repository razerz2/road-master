<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\TripStop;
use App\Models\Vehicle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetTripsAndOdometer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trips:reset {--force : Força a execução sem confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apaga todos os registros de percursos e redefine o KM dos veículos para o km_inicial';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  ATENÇÃO: Esta ação irá apagar TODOS os percursos e redefinir o KM de TODOS os veículos. Deseja continuar?')) {
                $this->info('Operação cancelada.');
                return Command::FAILURE;
            }
        }

        $this->info('Iniciando reset de percursos e odômetros...');

        try {
            DB::transaction(function () {
                // Contar percursos antes de deletar
                $tripsCount = Trip::count();
                $tripStopsCount = TripStop::count();

                // Apagar todas as paradas intermediárias
                $this->info('Apagando paradas intermediárias...');
                TripStop::truncate();
                $this->info("✓ {$tripStopsCount} parada(s) intermediária(s) apagada(s)");

                // Apagar todos os percursos
                $this->info('Apagando percursos...');
                Trip::truncate();
                $this->info("✓ {$tripsCount} percurso(s) apagado(s)");

                // Redefinir KM dos veículos para km_inicial
                $this->info('Redefinindo KM dos veículos...');
                $vehicles = Vehicle::all();
                $updatedCount = 0;

                foreach ($vehicles as $vehicle) {
                    $kmInicial = $vehicle->km_inicial ?? 0;
                    $vehicle->update(['current_odometer' => $kmInicial]);
                    $updatedCount++;
                    $this->line("  ✓ {$vehicle->name} ({$vehicle->plate}): KM redefinido para {$kmInicial} km");
                }

                $this->info("✓ {$updatedCount} veículo(s) atualizado(s)");
            });

            $this->newLine();
            $this->info('✅ Reset concluído com sucesso!');
            $this->info('Todos os percursos foram apagados e os odômetros dos veículos foram redefinidos para o km_inicial.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Erro ao executar reset: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
