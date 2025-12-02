<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'link',
        'read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Marcar notificação como lida
     */
    public function markAsRead(): void
    {
        if (!$this->read) {
            $this->update([
                'read' => true,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Scope para notificações não lidas
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope para notificações lidas
     */
    public function scopeRead($query)
    {
        return $query->where('read', true);
    }

    /**
     * Criar uma nova notificação de forma facilitada
     * 
     * @param int $userId ID do usuário
     * @param string $type Tipo da notificação (info, success, warning, error)
     * @param string $title Título da notificação
     * @param string $message Mensagem da notificação
     * @param string|null $link Link opcional para redirecionamento
     * @return Notification
     */
    public static function createNotification($userId, $type, $title, $message, $link = null)
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'read' => false,
        ]);
    }

    /**
     * Criar notificação para múltiplos usuários
     * 
     * @param array $userIds Array de IDs de usuários
     * @param string $type Tipo da notificação
     * @param string $title Título da notificação
     * @param string $message Mensagem da notificação
     * @param string|null $link Link opcional
     * @return void
     */
    public static function createForUsers(array $userIds, $type, $title, $message, $link = null)
    {
        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'link' => $link,
                'read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        if (!empty($notifications)) {
            self::insert($notifications);
        }
    }
}
