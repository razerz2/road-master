# Configuração do Cron no Ubuntu para Notificações Automáticas

## 📋 Visão Geral

Para que as notificações de revisão e obrigações legais funcionem automaticamente no servidor Ubuntu, é necessário configurar o **Cron** do Laravel. O sistema já está configurado para executar os comandos automaticamente, mas precisa que o cron esteja ativo.

## ⚙️ Passo a Passo

### 1. Acessar o Servidor Ubuntu

Conecte-se ao servidor via SSH:

```bash
ssh usuario@seu-servidor.com
```

### 2. Localizar o Caminho do Projeto

Navegue até o diretório do projeto Laravel:

```bash
cd /var/www/road-master
# ou o caminho onde seu projeto está instalado
```

### 3. Verificar o Caminho do PHP

Descubra onde o PHP está instalado:

```bash
which php
# Exemplo de saída: /usr/bin/php
```

### 4. Editar o Crontab

Abra o crontab do usuário (recomendado) ou do root:

```bash
# Para o usuário atual (recomendado)
crontab -e

# OU para root (se necessário)
sudo crontab -e
```

### 5. Adicionar a Linha do Cron

Adicione a seguinte linha ao final do arquivo crontab:

```bash
* * * * * cd /var/www/road-master && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

**Importante**: Ajuste os caminhos conforme seu ambiente:
- `/var/www/road-master` → caminho completo do seu projeto
- `/usr/bin/php` → caminho do PHP (use o resultado do comando `which php`)

### 6. Salvar e Sair

- Se estiver usando **nano**: `Ctrl + X`, depois `Y`, depois `Enter`
- Se estiver usando **vi**: `Esc`, depois `:wq`, depois `Enter`

### 7. Verificar se o Cron Foi Configurado

Liste os crons ativos:

```bash
crontab -l
```

Você deve ver a linha que acabou de adicionar.

### 8. Verificar se o Cron Está Rodando

Verifique o status do serviço cron:

```bash
sudo systemctl status cron
```

Se não estiver rodando, inicie:

```bash
sudo systemctl start cron
sudo systemctl enable cron  # Para iniciar automaticamente no boot
```

## 🔍 Testando a Configuração

### Teste Manual do Schedule

Execute manualmente para verificar se está funcionando:

```bash
cd /var/www/road-master
php artisan schedule:run
```

Você deve ver mensagens indicando que os comandos foram executados (ou que não havia nada para executar no momento).

### Teste dos Comandos Individualmente

Teste cada comando separadamente:

```bash
# Testar notificações de revisão
php artisan reviews:check --force

# Testar notificações de obrigações legais
php artisan mandatory-events:check --force
```

### Verificar Logs

Os logs do Laravel podem ajudar a diagnosticar problemas:

```bash
tail -f storage/logs/laravel.log
```

## ⚙️ Configurações do Sistema

### Horários de Execução

Os horários podem ser configurados no sistema através da página de **Configurações**:

- **Horário de verificação de revisões**: `review_check_time` (padrão: 08:00)
- **Horário de verificação de obrigações legais**: `mandatory_event_check_time` (padrão: 08:00)
- **Frequência de verificação**: `notification_check_frequency` (diária ou semanal)

### Habilitar/Desabilitar Notificações

Na página de **Configurações**, você pode:
- Habilitar/desabilitar notificações: `notifications_enabled`
- Configurar quantos dias antes notificar: `mandatory_event_days_before`
- Configurar quantos KM antes notificar: `review_notification_km_before`

## 🐛 Solução de Problemas

### Cron Não Está Executando

1. **Verificar permissões do arquivo**:
   ```bash
   ls -la /var/www/road-master
   ```

2. **Verificar logs do cron**:
   ```bash
   sudo tail -f /var/log/syslog | grep CRON
   ```

3. **Verificar se o PHP está acessível**:
   ```bash
   /usr/bin/php -v
   ```

4. **Testar caminho completo**:
   ```bash
   cd /var/www/road-master && /usr/bin/php artisan schedule:run
   ```

### Comandos Não Estão Sendo Executados

1. **Verificar se as notificações estão habilitadas**:
   - Acesse: Configurações → Notificações
   - Verifique se `notifications_enabled` está marcado

2. **Verificar se há dados para notificar**:
   - Crie uma revisão ou obrigação de teste
   - Execute manualmente: `php artisan reviews:check --force`

3. **Verificar permissões de escrita**:
   ```bash
   sudo chown -R www-data:www-data /var/www/road-master/storage
   sudo chmod -R 775 /var/www/road-master/storage
   ```

### Erro de Permissão

Se houver erros de permissão, ajuste as permissões:

```bash
# Ajustar dono (ajuste 'www-data' conforme seu servidor)
sudo chown -R www-data:www-data /var/www/road-master

# Ajustar permissões
sudo chmod -R 755 /var/www/road-master
sudo chmod -R 775 /var/www/road-master/storage
sudo chmod -R 775 /var/www/road-master/bootstrap/cache
```

## 📝 Exemplo Completo de Crontab

Aqui está um exemplo completo de como seu crontab pode ficar:

```bash
# Laravel Scheduler - Executa a cada minuto
* * * * * cd /var/www/road-master && /usr/bin/php artisan schedule:run >> /dev/null 2>&1

# Opcional: Logs (descomente para debug)
# * * * * * cd /var/www/road-master && /usr/bin/php artisan schedule:run >> /var/www/road-master/storage/logs/scheduler.log 2>&1
```

## 🔐 Segurança

### Usuário Recomendado

É recomendado executar o cron com o mesmo usuário do servidor web (geralmente `www-data`):

```bash
sudo crontab -u www-data -e
```

### Alternativa: Usar Supervisor

Para ambientes de produção mais robustos, considere usar **Supervisor** para gerenciar o scheduler:

```bash
sudo apt-get install supervisor
```

Crie um arquivo de configuração em `/etc/supervisor/conf.d/laravel-scheduler.conf`:

```ini
[program:laravel-scheduler]
process_name=%(program_name)s
command=/usr/bin/php /var/www/road-master/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/road-master/storage/logs/scheduler.log
```

Depois:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-scheduler
```

## ✅ Checklist de Verificação

- [ ] Cron configurado no crontab
- [ ] Serviço cron rodando (`systemctl status cron`)
- [ ] Caminho do projeto correto
- [ ] Caminho do PHP correto
- [ ] Permissões de arquivo corretas
- [ ] Notificações habilitadas no sistema
- [ ] Teste manual executado com sucesso
- [ ] Logs verificados (sem erros)

## 📞 Suporte

Se após seguir todos os passos ainda houver problemas:

1. Verifique os logs: `storage/logs/laravel.log`
2. Execute manualmente: `php artisan schedule:run`
3. Verifique as configurações no banco de dados: tabela `system_settings`

