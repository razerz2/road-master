<?php

namespace App\Services;

use App\Mail\NotificationEmail;
use App\Models\Notification;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationService
{
    /**
     * Verifica se as notificações por email estão habilitadas
     */
    public static function isEnabled(): bool
    {
        return SystemSetting::get('email_notifications_enabled', '0') === '1';
    }

    /**
     * Envia notificação por email para um usuário
     */
    public static function sendToUser(Notification $notification): bool
    {
        if (!self::isEnabled()) {
            return false;
        }

        try {
            $user = $notification->user;
            
            if (!$user || !$user->email) {
                return false;
            }

            Mail::to($user->email)->send(new NotificationEmail($notification));
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação por email', [
                'notification_id' => $notification->id,
                'user_id' => $notification->user_id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Envia notificações por email para múltiplos usuários
     */
    public static function sendToUsers(array $notifications): int
    {
        if (!self::isEnabled()) {
            return 0;
        }

        $sent = 0;
        
        foreach ($notifications as $notification) {
            if (self::sendToUser($notification)) {
                $sent++;
            }
        }
        
        return $sent;
    }

    /**
     * Obtém configurações de email do sistema
     */
    public static function getEmailSettings(): array
    {
        return [
            'enabled' => SystemSetting::get('email_notifications_enabled', '0') === '1',
            'from_address' => SystemSetting::get('email_from_address', config('mail.from.address')),
            'from_name' => SystemSetting::get('email_from_name', config('mail.from.name')),
        ];
    }
}

