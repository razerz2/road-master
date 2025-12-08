<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Notification $notification
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName = \App\Models\SystemSetting::get('app_name', config('app.name', 'Road Master'));
        $fromAddress = \App\Models\SystemSetting::get('email_from_address', config('mail.from.address'));
        $fromName = \App\Models\SystemSetting::get('email_from_name', config('mail.from.name', $appName));
        
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($fromAddress, $fromName),
            subject: $this->notification->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $appName = \App\Models\SystemSetting::get('app_name', config('app.name', 'Road Master'));
        $appUrl = config('app.url', url('/'));
        
        // Determinar cor baseada no tipo
        $colorMap = [
            'info' => '#3B82F6',      // Azul
            'success' => '#10B981',   // Verde
            'warning' => '#F59E0B',   // Amarelo/Laranja
            'error' => '#EF4444',     // Vermelho
        ];
        
        $color = $colorMap[$this->notification->type] ?? $colorMap['info'];
        
        return new Content(
            view: 'emails.notification',
            with: [
                'notification' => $this->notification,
                'appName' => $appName,
                'appUrl' => $appUrl,
                'color' => $color,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

