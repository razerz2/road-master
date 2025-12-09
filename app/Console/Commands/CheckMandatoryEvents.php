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
    protected $signature = 'mandatory-events:check';

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

        // Buscar todas as obrigações não resolvidas e não notificadas
        $events = VehicleMandatoryEvent::where('resolved', false)
            ->where('notified', false)
            ->whereDate('due_date', '<=', now()->addDays($daysBefore))
            ->with('vehicle')
            ->get();

        foreach ($events as $event) {
            $eventsChecked++;

            $vehicle = $event->vehicle;
            
            if (!$vehicle || !$vehicle->active) {
                continue;
            }

            // Verificar se deve disparar notificação
            if ($event->shouldNotify()) {
                // Buscar usuários para notificar
                if ($notifyOnlyAdmins) {
                    $userIds = \App\Models\User::where('role', 'admin')
                        ->where('active', true)
                        ->pluck('id')
                        ->toArray();
                } else {
                    // Buscar usuários relacionados ao veículo
                    $userIds = $vehicle->users()->pluck('users.id')->toArray();

                    // Se não houver usuários específicos, notificar todos os admins
                    if (empty($userIds)) {
                        $userIds = \App\Models\User::where('role', 'admin')
                            ->where('active', true)
                            ->pluck('id')
                            ->toArray();
                    }
                }

                if (!empty($userIds)) {
                    $typeName = $event->type_name;
                    $dueDateFormatted = $event->due_date->format('d/m/Y');
                    $daysUntilDue = now()->diffInDays($event->due_date, false);
                    
                    $title = 'Pagamento obrigatório próximo do vencimento';
                    $message = "O veículo {$vehicle->name} ({$vehicle->plate}) possui {$typeName} com vencimento em {$dueDateFormatted}";
                    if ($daysUntilDue > 0) {
                        $message .= " (vence em {$daysUntilDue} dia(s)).";
                    } else {
                        $message .= " (vencido há " . abs($daysUntilDue) . " dia(s)).";
                    }
                    $link = route('mandatory-events.index');

                    // Criar notificações para os usuários
                    Notification::createForUsers(
                        $userIds,
                        'warning',
                        $title,
                        $message,
                        $link
                    );

                    // Marcar como notificado
                    $event->update(['notified' => true]);

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
