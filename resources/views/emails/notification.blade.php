<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            background-color: {{ $color }};
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 30px 20px;
        }
        .notification-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 20px;
            background-color: {{ $color }}20;
            color: {{ $color }};
        }
        .notification-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1a1a1a;
        }
        .notification-message {
            font-size: 16px;
            color: #4a4a4a;
            margin-bottom: 25px;
            line-height: 1.8;
        }
        .notification-link {
            display: inline-block;
            padding: 12px 24px;
            background-color: {{ $color }};
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 10px;
        }
        .notification-link:hover {
            opacity: 0.9;
        }
        .email-footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e5e5e5;
        }
        .email-footer a {
            color: {{ $color }};
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>{{ $appName }}</h1>
        </div>
        
        <div class="email-body">
            <span class="notification-type">{{ ucfirst($notification->type) }}</span>
            
            <h2 class="notification-title">{{ $notification->title }}</h2>
            
            <div class="notification-message">
                {!! nl2br(e($notification->message)) !!}
            </div>
            
            @if($notification->link)
            <a href="{{ $appUrl }}{{ $notification->link }}" class="notification-link">
                Ver Detalhes
            </a>
            @endif
        </div>
        
        <div class="email-footer">
            <p>Esta é uma notificação automática do sistema {{ $appName }}.</p>
            <p>
                <a href="{{ $appUrl }}">Acessar o sistema</a>
            </p>
        </div>
    </div>
</body>
</html>

