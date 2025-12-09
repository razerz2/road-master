<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adicionar configurações de revisão
        SystemSetting::set(
            'review_notification_km_before',
            '0',
            'integer',
            'reviews',
            'KM de antecedência para notificar revisões (0 = notificar no KM exato)'
        );

        SystemSetting::set(
            'review_check_time',
            '08:00',
            'string',
            'reviews',
            'Horário para verificar revisões (formato HH:mm)'
        );

        SystemSetting::set(
            'review_notify_only_admins',
            '0',
            'boolean',
            'reviews',
            'Notificar apenas administradores (0 = notificar todos os usuários do veículo)'
        );

        // Adicionar configurações de obrigações legais
        SystemSetting::set(
            'mandatory_event_days_before',
            '10',
            'integer',
            'mandatory_events',
            'Dias de antecedência para notificar obrigações legais'
        );

        SystemSetting::set(
            'mandatory_event_check_time',
            '08:00',
            'string',
            'mandatory_events',
            'Horário para verificar obrigações legais (formato HH:mm)'
        );

        SystemSetting::set(
            'mandatory_event_notify_only_admins',
            '0',
            'boolean',
            'mandatory_events',
            'Notificar apenas administradores (0 = notificar todos os usuários do veículo)'
        );

        // Configurações gerais de notificações
        SystemSetting::set(
            'notifications_enabled',
            '1',
            'boolean',
            'notifications',
            'Habilitar notificações automáticas do sistema'
        );

        SystemSetting::set(
            'notification_check_frequency',
            'daily',
            'string',
            'notifications',
            'Frequência de verificação (daily, weekly)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover configurações
        SystemSetting::whereIn('key', [
            'review_notification_km_before',
            'review_check_time',
            'review_notify_only_admins',
            'mandatory_event_days_before',
            'mandatory_event_check_time',
            'mandatory_event_notify_only_admins',
            'notifications_enabled',
            'notification_check_frequency',
        ])->delete();
    }
};
