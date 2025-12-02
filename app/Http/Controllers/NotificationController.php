<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Listar todas as notificações do usuário
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->notifications()->orderBy('created_at', 'desc');
        
        // Filtro por tipo
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        
        // Filtro por status (lida/não lida)
        if ($request->has('status')) {
            if ($request->status === 'unread') {
                $query->where('read', false);
            } elseif ($request->status === 'read') {
                $query->where('read', true);
            }
        }
        
        $notifications = $query->paginate(15);
        
        // Contar notificações não lidas
        $unreadCount = $user->notifications()->unread()->count();
        
        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mostrar uma notificação específica
     */
    public function show(Notification $notification)
    {
        // Verificar se a notificação pertence ao usuário
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Marcar como lida
        $notification->markAsRead();
        
        // Se tiver link, redirecionar
        if ($notification->link) {
            return redirect($notification->link);
        }
        
        return view('notifications.show', compact('notification'));
    }

    /**
     * Marcar notificação como lida
     */
    public function markAsRead(Notification $notification)
    {
        // Verificar se a notificação pertence ao usuário
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'unread_count' => Auth::user()->notifications()->unread()->count(),
        ]);
    }

    /**
     * Marcar todas as notificações como lidas
     */
    public function markAllAsRead()
    {
        Auth::user()->notifications()->unread()->update([
            'read' => true,
            'read_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Deletar uma notificação
     */
    public function destroy(Notification $notification)
    {
        // Verificar se a notificação pertence ao usuário
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }
        
        $notification->delete();
        
        return redirect()->route('notifications.index')
            ->with('success', 'Notificação excluída com sucesso!');
    }

    /**
     * API: Obter contagem de notificações não lidas
     */
    public function unreadCount()
    {
        $count = Auth::user()->notifications()->unread()->count();
        
        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * API: Obter últimas notificações não lidas
     */
    public function latest()
    {
        $notifications = Auth::user()->notifications()
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json($notifications);
    }
}
