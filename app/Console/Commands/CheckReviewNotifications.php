<?php

namespace App\Console\Commands;

use App\Models\ReviewNotification;
use App\Models\Vehicle;
use App\Models\Notification;
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
        $this->info('Verificando notificações de revisão...');

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

            // Verificar se deve disparar notificação
            if ($reviewNotification->shouldNotify($currentOdometer)) {
                // Buscar usuários relacionados ao veículo
                $userIds = $vehicle->users()->pluck('users.id')->toArray();

                // Se não houver usuários específicos, notificar todos os admins
                if (empty($userIds)) {
                    $userIds = \App\Models\User::where('role', 'admin')
                        ->where('active', true)
                        ->pluck('id')
                        ->toArray();
                }

                if (!empty($userIds)) {
                    $reviewTypeName = $reviewNotification->review_type_name;
                    $name = $reviewNotification->name ?: $reviewTypeName;
                    
                    $title = "Revisão Necessária: {$name}";
                    $message = "O veículo {$vehicle->name} ({$vehicle->plate}) atingiu {$currentOdometer} km e precisa de revisão: {$name}. KM configurado: {$reviewNotification->notification_km} km.";
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
