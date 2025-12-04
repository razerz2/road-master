<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\TripStop;
use App\Models\Vehicle;
use App\Models\Fueling;
use App\Models\Location;
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
    protected $description = 'Apaga todos os registros de percursos, abastecimentos, locais da importação e redefine o KM dos veículos para o km_inicial';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  ATENÇÃO: Esta ação irá apagar TODOS os percursos, abastecimentos, locais criados pela importação e redefinir o KM de TODOS os veículos. Deseja continuar?')) {
                $this->info('Operação cancelada.');
                return Command::FAILURE;
            }
        }

        $this->info('Iniciando reset de percursos e odômetros...');

        try {
            DB::transaction(function () {
                // Contar registros antes de deletar
                $tripsCount = Trip::count();
                $tripStopsCount = TripStop::count();
                $fuelingsCount = Fueling::count();

                // Apagar todas as paradas intermediárias
                $this->info('Apagando paradas intermediárias...');
                TripStop::truncate();
                $this->info("✓ {$tripStopsCount} parada(s) intermediária(s) apagada(s)");

                // Apagar todos os percursos
                $this->info('Apagando percursos...');
                Trip::truncate();
                $this->info("✓ {$tripsCount} percurso(s) apagado(s)");

                // Apagar todos os abastecimentos
                $this->info('Apagando abastecimentos...');
                Fueling::truncate();
                $this->info("✓ {$fuelingsCount} abastecimento(s) apagado(s)");

                // Apagar locais criados pela importação
                // Locais da importação são aqueles que não têm location_type_id definido
                // e não têm endereço completo (apenas nome)
                $this->info('Apagando locais criados pela importação...');
                $locationsToDelete = Location::whereNull('location_type_id')
                    ->where(function($query) {
                        $query->whereNull('address')
                            ->orWhere('address', '');
                    })
                    ->where(function($query) {
                        $query->whereNull('city')
                            ->orWhere('city', '');
                    });
                
                $locationsCount = $locationsToDelete->count();
                $locationsToDelete->delete();
                $this->info("✓ {$locationsCount} local(is) da importação apagado(s)");

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
            $this->info('Todos os percursos, abastecimentos e locais da importação foram apagados e os odômetros dos veículos foram redefinidos para o km_inicial.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Erro ao executar reset: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
