# Módulo de Notificações de Revisão

## Descrição

Este módulo permite configurar notificações automáticas para revisões de veículos baseadas em quilometragem. O sistema monitora o odômetro atual dos veículos e dispara notificações quando atingem o KM configurado para cada tipo de revisão.

## Funcionalidades

- ✅ Cadastro de notificações de revisão por veículo
- ✅ Múltiplos tipos de revisão (troca de óleo, manutenção, lavagem, etc.)
- ✅ Configuração de KM atual e KM para notificação
- ✅ Notificações automáticas quando o veículo atinge o KM configurado
- ✅ Controle de ativação/desativação de notificações
- ✅ Prevenção de notificações duplicadas
- ✅ Interface visual com indicadores de status (em dia, próximo, atrasado)

## Como Usar

### 1. Criar uma Notificação de Revisão

1. Acesse **Notificações de Revisão** no menu
2. Clique em **Nova Notificação**
3. Preencha os campos:
   - **Veículo**: Selecione o veículo
   - **Tipo de Revisão**: Escolha o tipo (troca de óleo, manutenção, etc.)
   - **Nome Personalizado** (opcional): Nome customizado para a revisão
   - **KM Atual**: KM atual do veículo (deixe em branco para usar o odômetro atual)
   - **KM para Notificação**: KM onde a notificação será disparada
   - **Descrição** (opcional): Informações adicionais
   - **Ativo**: Marque para ativar a notificação

### 2. Verificar Notificações

O sistema verifica automaticamente as notificações diariamente às 8h da manhã através do comando agendado `reviews:check`.

Para verificar manualmente, execute:
```bash
php artisan reviews:check
```

### 3. Gerenciar Notificações

- **Editar**: Atualize as informações da notificação
- **Ativar/Desativar**: Controle se a notificação está ativa
- **Excluir**: Remova notificações que não são mais necessárias

## Tipos de Revisão Disponíveis

- Troca de Óleo
- Revisão para Manutenção
- Lavagem
- Troca/Revisão de Pneus
- Revisão de Freios
- Revisão de Suspensão
- Troca de Filtro de Ar
- Troca de Filtro de Combustível
- Troca de Bateria
- Alinhamento e Balanceamento
- Outro

## Como Funciona

1. Quando você cria uma notificação de revisão, o sistema armazena:
   - O veículo relacionado
   - O tipo de revisão
   - O KM atual do veículo (quando configurado)
   - O KM onde a notificação será disparada

2. O comando `reviews:check` é executado diariamente e:
   - Busca todas as notificações ativas
   - Compara o odômetro atual do veículo com o KM configurado
   - Se o veículo atingiu ou ultrapassou o KM, dispara notificações para os usuários relacionados ao veículo
   - Marca o KM como notificado para evitar duplicatas

3. As notificações são criadas no sistema de notificações padrão e podem ser visualizadas no menu de notificações.

## Sugestões de Melhorias

### 1. Notificações por Email
- Enviar emails além das notificações internas
- Configurar templates de email personalizados
- Permitir escolher entre notificação interna, email ou ambos

### 2. Notificações Antecipadas
- Permitir configurar múltiplos alertas (ex: 1000km antes, 500km antes, no KM exato)
- Alertas progressivos conforme se aproxima do KM

### 3. Integração com Manutenções
- Ao criar uma manutenção, sugerir criar automaticamente a próxima notificação de revisão
- Vincular notificações com manutenções realizadas

### 4. Relatórios e Dashboard
- Dashboard com visão geral de todas as revisões pendentes
- Relatório de revisões por veículo
- Gráficos de histórico de revisões

### 5. Notificações por WhatsApp/SMS
- Integração com APIs de mensageria (Twilio, WhatsApp Business API)
- Enviar lembretes via mensagem

### 6. Recorrência Automática
- Após uma revisão, criar automaticamente a próxima notificação baseada em intervalo (ex: a cada 10.000km)
- Configurar intervalos padrão por tipo de revisão

### 7. Histórico de Notificações
- Registrar todas as notificações enviadas com data/hora
- Visualizar histórico de notificações por veículo

### 8. Filtros Avançados
- Filtrar por status (em dia, próximo, atrasado)
- Filtrar por intervalo de KM
- Exportar lista de revisões pendentes

### 9. Notificações para Múltiplos Usuários
- Permitir selecionar usuários específicos para cada notificação
- Grupos de notificação (ex: equipe de manutenção)

### 10. Integração com Calendário
- Sincronizar revisões com calendário (Google Calendar, Outlook)
- Lembretes no calendário

### 11. Anexos e Documentos
- Permitir anexar documentos relacionados à revisão
- Fotos, recibos, etc.

### 12. Custos Estimados
- Adicionar campo de custo estimado para cada tipo de revisão
- Alertar sobre custos acumulados

### 13. API REST
- Criar endpoints API para integração com outros sistemas
- Webhooks para notificações externas

### 14. Notificações Push
- Notificações push no navegador
- Integração com PWA (Progressive Web App)

### 15. Machine Learning
- Prever quando será necessário fazer revisões baseado no histórico
- Sugerir intervalos ideais de manutenção por tipo de veículo

## Configuração do Agendamento

O comando está configurado para executar diariamente às 8h. Para alterar, edite o arquivo `bootstrap/app.php`:

```php
->withSchedule(function (Schedule $schedule): void {
    // Alterar horário ou frequência
    $schedule->command('reviews:check')->dailyAt('08:00');
    // Ou a cada hora: ->hourly();
    // Ou a cada 6 horas: ->everySixHours();
})
```

**Importante**: Para que o agendamento funcione, é necessário configurar o Cron no servidor:

```bash
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

## Estrutura de Arquivos

- `app/Models/ReviewNotification.php` - Model principal
- `app/Http/Controllers/ReviewNotificationController.php` - Controller CRUD
- `app/Console/Commands/CheckReviewNotifications.php` - Comando de verificação
- `app/Policies/ReviewNotificationPolicy.php` - Políticas de acesso
- `database/migrations/*_create_review_notifications_table.php` - Migration
- `resources/views/review-notifications/` - Views

## Permissões

O módulo utiliza o sistema de permissões existente. Certifique-se de criar um módulo "review_notifications" nas configurações do sistema e atribuir permissões aos usuários.

## Troubleshooting

### Notificações não estão sendo enviadas

1. Verifique se o comando está agendado corretamente:
   ```bash
   php artisan schedule:list
   ```

2. Execute manualmente para testar:
   ```bash
   php artisan reviews:check
   ```

3. Verifique se há notificações ativas no banco de dados

4. Verifique se os veículos têm usuários relacionados ou se há admins no sistema

### Notificações duplicadas

O sistema previne duplicatas verificando o campo `last_notified_km`. Se ainda assim houver duplicatas, verifique:
- Se o odômetro do veículo está sendo atualizado corretamente
- Se há múltiplas notificações para o mesmo tipo no mesmo veículo

