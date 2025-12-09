<?php

namespace App\Console\Commands;

use App\Models\ReviewNotification;
use App\Models\Vehicle;
use App\Models\Notification;
use App\Models\SystemSetting;
use Illuminate\Console\Command;

class CheckReviewNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reviews:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica veículos que atingiram o KM de revisão e dispara notificações';

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

        $this->info('Verificando notificações de revisão...');

        // Obter configurações
        $kmBefore = (int) SystemSetting::get('review_notification_km_before', '0');
        $notifyOnlyAdmins = SystemSetting::get('review_notify_only_admins', '0') === '1';

        $notificationsSent = 0;
        $notificationsChecked = 0;

        // Buscar todas as notificações de revisão ativas
        $reviewNotifications = ReviewNotification::active()
            ->with('vehicle')
            ->get();

        foreach ($reviewNotifications as $reviewNotification) {
            $notificationsChecked++;

            $vehicle = $reviewNotification->vehicle;
            
            if (!$vehicle || !$vehicle->active) {
                continue;
            }

            $currentOdometer = $vehicle->current_odometer ?? 0;
            
            // Ajustar KM de notificação considerando a antecedência configurada
            $notificationKm = $reviewNotification->notification_km - $kmBefore;

            // Verificar se deve disparar notificação (considerando antecedência)
            $shouldNotify = $reviewNotification->active 
                && $currentOdometer >= $notificationKm
                && ($reviewNotification->last_notified_km === null || $currentOdometer > $reviewNotification->last_notified_km);

            if ($shouldNotify) {
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
                    $reviewTypeName = $reviewNotification->review_type_name;
                    $name = $reviewNotification->name ?: $reviewTypeName;
                    
                    // Mensagem com informação sobre antecedência
                    $kmRemaining = $reviewNotification->notification_km - $currentOdometer;
                    if ($kmRemaining > 0) {
                        $message = "O veículo {$vehicle->name} ({$vehicle->plate}) está próximo de precisar de revisão: {$name}. Faltam {$kmRemaining} km para atingir o KM configurado ({$reviewNotification->notification_km} km). KM atual: {$currentOdometer} km.";
                    } else {
                        $message = "O veículo {$vehicle->name} ({$vehicle->plate}) atingiu {$currentOdometer} km e precisa de revisão: {$name}. KM configurado: {$reviewNotification->notification_km} km.";
                    }
                    
                    $title = "Revisão Necessária: {$name}";
                    $link = route('vehicles.show', $vehicle->id);

                    // Criar notificações para os usuários
                    Notification::createForUsers(
                        $userIds,
                        'warning',
                        $title,
                        $message,
                        $link
                    );

                    // Marcar como notificado
                    $reviewNotification->markAsNotified($currentOdometer);

                    $notificationsSent++;
                    $this->line("✓ Notificação enviada para veículo {$vehicle->name} - {$name}");
                }
            }
        }

        $this->info("Verificação concluída!");
        $this->info("Notificações verificadas: {$notificationsChecked}");
        $this->info("Notificações enviadas: {$notificationsSent}");

        return Command::SUCCESS;
    }
}
