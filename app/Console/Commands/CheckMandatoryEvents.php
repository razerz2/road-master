<?php

namespace App\Console\Commands;

use App\Models\VehicleMandatoryEvent;
use App\Models\Vehicle;
use App\Models\Notification;
use App\Models\SystemSetting;
use Illuminate\Console\Command;

class CheckMandatoryEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mandatory-events:check {--force : Força o envio de notificações mesmo se já foram enviadas hoje (útil para testes)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica vencimentos de IPVA, Licenciamento e Multas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Verificar se notificações estão habilitadas
        $notificationsEnabled = SystemSetting::get('notifications_enabled', '1') === '1';
        if (!$notificationsEnabled) {
            $this->info('Notificações automáticas estão desabilitadas nas configurações.');
            return Command::SUCCESS;
        }

        $this->info('Verificando obrigações legais próximas do vencimento...');

        // Obter configurações
        $daysBefore = (int) SystemSetting::get('mandatory_event_days_before', '10');
        $notifyOnlyAdmins = SystemSetting::get('mandatory_event_notify_only_admins', '0') === '1';

        $notificationsSent = 0;
        $eventsChecked = 0;

        $force = $this->option('force');
        
        // Buscar todas as obrigações não resolvidas
        $query = VehicleMandatoryEvent::where('resolved', false)
            ->whereDate('due_date', '<=', now()->addDays($daysBefore));
        
        $events = $query->with('vehicle')->get();

        foreach ($events as $event) {
            $eventsChecked++;

            $vehicle = $event->vehicle;
            
            if (!$vehicle || !$vehicle->active) {
                continue;
            }

            // Não enviar notificações se a obrigação já foi resolvida
            if ($event->resolved) {
                continue;
            }

            // Verificar se deve disparar notificação
            // Continuar enviando até que seja resolvida
            // Enviar notificação se:
            // 1. Não foi resolvida ainda
            // 2. Está dentro do prazo (due_date <= hoje + daysBefore)
            // 3. Não foi notificada hoje (evita múltiplas notificações no mesmo dia, mas continua enviando diariamente)
            //    OU se --force foi usado (para testes)
            $lastNotifiedDate = $event->last_notified_at 
                ? \Carbon\Carbon::parse($event->last_notified_at)->startOfDay() 
                : null;
            $today = now()->startOfDay();
            $daysUntilDue = now()->diffInDays($event->due_date, false);
            
            $shouldNotify = !$event->resolved 
                && $daysUntilDue <= $daysBefore
                && ($force || $lastNotifiedDate === null || $lastNotifiedDate->lt($today));
            
            if ($shouldNotify) {
                // Buscar usuários para notificar
                if ($notifyOnlyAdmins) {
                    // Se configurado para notificar apenas admins
                    $userIds = \App\Models\User::where('role', 'admin')
                        ->where('active', true)
                        ->pluck('id')
                        ->toArray();
                } else {
                    // Buscar usuários relacionados ao veículo
                    $userIds = $vehicle->users()->pluck('users.id')->toArray();

                    // Sempre incluir admins nas notificações
                    $adminIds = \App\Models\User::where('role', 'admin')
                        ->where('active', true)
                        ->pluck('id')
                        ->toArray();
                    
                    // Combinar usuários do veículo com admins (sem duplicatas)
                    $userIds = array_unique(array_merge($userIds, $adminIds));
                }

                if (!empty($userIds)) {
                    $typeName = $event->type_name;
                    $dueDateFormatted = $event->due_date->format('d/m/Y');
                    $daysUntilDue = (int) now()->diffInDays($event->due_date, false);
                    
                    $title = 'Pagamento obrigatório próximo do vencimento';
                    $message = "O veículo {$vehicle->name} ({$vehicle->plate}) possui {$typeName} com vencimento em {$dueDateFormatted}";
                    if ($daysUntilDue > 0) {
                        $message .= " (vence em {$daysUntilDue} dia(s)).";
                    } else {
                        $message .= " (vencido há " . abs($daysUntilDue) . " dia(s)).";
                    }
                    // Usar URL relativa para evitar problemas com diferentes hosts/portas
                    $link = '/mandatory-events';

                    // Criar notificações para os usuários
                    Notification::createForUsers(
                        $userIds,
                        'warning',
                        $title,
                        $message,
                        $link
                    );

                    // Marcar como notificado (atualizar last_notified_at para controle diário)
                    $event->update([
                        'notified' => true,
                        'last_notified_at' => now(),
                    ]);

                    $notificationsSent++;
                    $this->line("✓ Notificação enviada para veículo {$vehicle->name} - {$typeName}");
                }
            }
        }

        $this->info("Verificação concluída!");
        $this->info("Obrigações verificadas: {$eventsChecked}");
        $this->info("Notificações enviadas: {$notificationsSent}");

        return Command::SUCCESS;
    }
}
