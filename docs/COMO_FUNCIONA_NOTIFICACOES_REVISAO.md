# Como Funciona o Sistema de Notificações de Revisão

## 📋 Visão Geral

O sistema verifica automaticamente se algum veículo atingiu o KM configurado para revisão e dispara notificações para os usuários responsáveis.

## ⏰ Quando é Verificado?

### Agendamento Automático
- **Frequência**: Diariamente
- **Horário**: 8h da manhã
- **Comando**: `php artisan reviews:check`

O agendamento está configurado no arquivo `bootstrap/app.php`:

```php
->withSchedule(function (Schedule $schedule): void {
    $schedule->command('reviews:check')->dailyAt('08:00');
})
```

### Verificação Manual
Você também pode executar manualmente a qualquer momento:

```bash
php artisan reviews:check
```

## 🔍 Como Funciona a Verificação?

### Passo 1: Buscar Revisões Ativas
O sistema busca todas as revisões que estão **ativas** (`active = true`):

```php
$reviewNotifications = ReviewNotification::active()
    ->with('vehicle')
    ->get();
```

### Passo 2: Verificar Cada Revisão
Para cada revisão encontrada, o sistema:

1. **Verifica se o veículo está ativo**
   ```php
   if (!$vehicle || !$vehicle->active) {
       continue; // Pula se veículo inativo
   }
   ```

2. **Obtém o odômetro atual do veículo**
   ```php
   $currentOdometer = $vehicle->current_odometer ?? 0;
   ```

3. **Verifica se deve disparar notificação**
   O método `shouldNotify()` verifica três condições:

   ```php
   public function shouldNotify(int $currentOdometer): bool
   {
       // 1. Revisão deve estar ativa
       if (!$this->active) {
           return false;
       }

       // 2. Odômetro atual deve ser >= KM configurado
       if ($currentOdometer < $this->notification_km) {
           return false;
       }

       // 3. Evitar duplicatas - só notifica se KM atual > último KM notificado
       if ($this->last_notified_km !== null && 
           $currentOdometer <= $this->last_notified_km) {
           return false;
       }

       return true;
   }
   ```

### Passo 3: Identificar Usuários para Notificar

O sistema busca usuários de duas formas:

1. **Usuários relacionados ao veículo** (tabela `user_vehicle`)
   ```php
   $userIds = $vehicle->users()->pluck('users.id')->toArray();
   ```

2. **Se não houver usuários específicos, notifica todos os admins**
   ```php
   if (empty($userIds)) {
       $userIds = User::where('role', 'admin')
           ->where('active', true)
           ->pluck('id')
           ->toArray();
   }
   ```

### Passo 4: Criar as Notificações

Se houver usuários para notificar, o sistema cria notificações com:

**Título:**
```
Revisão Necessária: {Nome da Revisão}
```

**Mensagem:**
```
O veículo {Nome do Veículo} ({Placa}) atingiu {KM Atual} km e precisa de revisão: {Nome da Revisão}. KM configurado: {KM Configurado} km.
```

**Exemplo Real:**
```
Título: Revisão Necessária: Troca de Óleo
Mensagem: O veículo Fiat Uno (ABC-1234) atingiu 15000 km e precisa de revisão: Troca de Óleo. KM configurado: 15000 km.
```

**Tipo:** `warning` (amarelo/laranja)

**Link:** Link direto para a página do veículo

### Passo 5: Marcar como Notificado

Após criar as notificações, o sistema atualiza o campo `last_notified_km`:

```php
$reviewNotification->markAsNotified($currentOdometer);
```

Isso evita que a mesma notificação seja enviada múltiplas vezes para o mesmo KM.

## 📨 Como a Mensagem é Transmitida?

### 1. Criação da Notificação

As notificações são criadas usando o método `Notification::createForUsers()`:

```php
Notification::createForUsers(
    $userIds,           // Array de IDs dos usuários
    'warning',         // Tipo: warning (amarelo)
    $title,            // Título da notificação
    $message,          // Mensagem completa
    $link              // Link para o veículo
);
```

### 2. Armazenamento no Banco

As notificações são salvas na tabela `notifications` com:
- `user_id`: ID do usuário que receberá
- `type`: `warning`
- `title`: Título da notificação
- `message`: Mensagem completa
- `link`: URL para a página do veículo
- `read`: `false` (não lida)
- `read_at`: `null`

### 3. Exibição para o Usuário

As notificações aparecem:

1. **No ícone de sino** no menu superior (com contador de não lidas)
2. **No dropdown de notificações** (últimas 5 não lidas)
3. **Na página de notificações** (`/notifications`)

### 4. Visualização

Quando o usuário clica na notificação:
- A notificação é marcada como lida
- O usuário é redirecionado para a página do veículo (via `link`)

## 🔄 Fluxo Completo

```
┌─────────────────────────────────────┐
│  Agendamento (8h diariamente)      │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Comando: reviews:check             │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Buscar revisões ativas              │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Para cada revisão:                  │
│  1. Verificar veículo ativo          │
│  2. Obter odômetro atual             │
│  3. Verificar se deve notificar      │
└──────────────┬──────────────────────┘
               │
               ▼
        ┌──────┴──────┐
        │             │
    SIM │             │ NÃO
        │             │
        ▼             ▼
┌──────────────┐  ┌──────────────┐
│ Buscar       │  │ Pular        │
│ usuários     │  │ revisão      │
└──────┬───────┘  └──────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│ Criar notificações para usuários    │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│ Marcar como notificado              │
│ (last_notified_km = KM atual)       │
└─────────────────────────────────────┘
```

## 🛡️ Prevenção de Duplicatas

O sistema evita notificações duplicadas de três formas:

1. **Verificação de KM já notificado**
   - Se `last_notified_km` existe e `currentOdometer <= last_notified_km`, não notifica

2. **Verificação de status ativo**
   - Apenas revisões com `active = true` são verificadas

3. **Verificação de veículo ativo**
   - Apenas veículos com `active = true` são considerados

## 📊 Exemplo Prático

### Cenário:
- Veículo: Fiat Uno (ABC-1234)
- Revisão: Troca de Óleo
- KM configurado: 15.000 km
- KM atual do veículo: 15.050 km
- Usuários relacionados: João e Maria

### Processo:

1. **8h da manhã**: Comando executa
2. **Verificação**: 
   - Revisão está ativa? ✅
   - Veículo está ativo? ✅
   - KM atual (15.050) >= KM configurado (15.000)? ✅
   - KM atual (15.050) > último KM notificado (null ou < 15.050)? ✅
3. **Ação**: 
   - Cria 2 notificações (uma para João, uma para Maria)
   - Marca `last_notified_km = 15050`
4. **Resultado**:
   - João e Maria recebem notificação no sistema
   - Notificação aparece no sino com badge vermelho
   - Ao clicar, são redirecionados para a página do veículo

### Se o comando executar novamente no mesmo dia:
- KM atual ainda é 15.050
- `last_notified_km` é 15.050
- `currentOdometer (15050) <= last_notified_km (15050)` = ✅
- **Não cria nova notificação** (evita duplicata)

### Se o veículo rodar mais:
- KM atual passa para 15.100
- `currentOdometer (15100) > last_notified_km (15050)` = ✅
- **Cria nova notificação** (atualiza o alerta)

## ⚙️ Configuração do Cron (Produção)

Para que o agendamento funcione em produção, configure o Cron no servidor:

```bash
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

Isso executa o scheduler do Laravel a cada minuto, que por sua vez executa os comandos agendados no horário correto.

## 🔍 Logs e Debug

Para ver o que está acontecendo, execute manualmente:

```bash
php artisan reviews:check
```

Você verá:
```
Verificando notificações de revisão...
✓ Notificação enviada para veículo Fiat Uno - Troca de Óleo
Verificação concluída!
Notificações verificadas: 5
Notificações enviadas: 1
```

## 📝 Resumo

1. **Quando**: Diariamente às 8h (ou manualmente)
2. **O que verifica**: Revisões ativas vs odômetro atual dos veículos
3. **Condição**: KM atual >= KM configurado E KM atual > último KM notificado
4. **Para quem**: Usuários relacionados ao veículo ou admins
5. **Onde aparece**: Ícone de sino no menu + página de notificações
6. **Proteção**: Sistema evita duplicatas automaticamente

