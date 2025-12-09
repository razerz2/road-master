<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Email</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">✅ Teste de Email</h1>
    </div>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0;">
        <p style="font-size: 16px; margin-bottom: 20px;">
            Olá!
        </p>
        
        <p style="font-size: 16px; margin-bottom: 20px;">
            Este é um email de teste para validar as configurações de SMTP do sistema <strong>Road Master</strong>.
        </p>
        
        <div style="background: white; padding: 20px; border-radius: 5px; border-left: 4px solid #667eea; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Remetente:</strong> {{ $fromName }}</p>
            <p style="margin: 5px 0;"><strong>Email:</strong> {{ $fromAddress }}</p>
            <p style="margin: 5px 0;"><strong>Data/Hora:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
        
        <p style="font-size: 16px; margin-top: 30px; color: #10B981;">
            <strong>✓ Se você recebeu este email, significa que as configurações de SMTP estão funcionando corretamente!</strong>
        </p>
        
        <p style="font-size: 14px; color: #666; margin-top: 30px;">
            Este é um email automático de teste. Não é necessário responder.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: #999; font-size: 12px;">
        <p>Road Master - Sistema de Controle de KM e Veículos</p>
    </div>
</body>
</html>

