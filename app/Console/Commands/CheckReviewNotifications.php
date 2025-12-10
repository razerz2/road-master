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
    protected $signature = 'reviews:check {--force : Força o envio de notificações mesmo se já foram enviadas hoje (útil para testes)}';

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

            // Não enviar notificações se a revisão já foi realizada
            if ($reviewNotification->completed_at !== null) {
                continue;
            }

            // Ajustar KM de notificação considerando a antecedência configurada
            $notificationKm = $reviewNotification->notification_km - $kmBefore;

            // Verificar se deve disparar notificação (considerando antecedência)
            // Continuar enviando até que seja marcada como realizada
            // Enviar notificação se:
            // 1. Está ativa
            // 2. KM atual >= KM de notificação
            // 3. Não foi realizada ainda
            // 4. Não foi notificada hoje (evita múltiplas notificações no mesmo dia, mas continua enviando diariamente)
            //    OU se --force foi usado (para testes)
            $force = $this->option('force');
            $lastNotifiedDate = $reviewNotification->last_notified_at 
                ? \Carbon\Carbon::parse($reviewNotification->last_notified_at)->startOfDay() 
                : null;
            $today = now()->startOfDay();
            
            $shouldNotify = $reviewNotification->active 
                && $currentOdometer >= $notificationKm
                && $reviewNotification->completed_at === null
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
                    // Link para a página de revisões do veículo filtrado
                    // Usar URL relativa para evitar problemas com diferentes hosts/portas
                    $link = '/review-notifications?vehicle_id=' . $vehicle->id;

                    // Criar notificações para os usuários
                    Notification::createForUsers(
                        $userIds,
                        'warning',
                        $title,
                        $message,
                        $link
                    );

                    // Atualizar timestamp de última notificação para controlar envio diário
                    // Isso permite que continue enviando notificações diariamente até ser marcada como realizada
                    $reviewNotification->update([
                        'last_notified_at' => now(),
                        'last_notified_km' => $currentOdometer,
                    ]);

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
