# Guia de Instalação e Configuração

## 📋 Pré-requisitos

Antes de começar, certifique-se de ter instalado:

- **PHP 8.2 ou superior** com as seguintes extensões:
  - BCMath
  - Ctype
  - cURL
  - DOM
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PCRE
  - PDO
  - Tokenizer
  - XML
- **Composer** (gerenciador de dependências PHP)
- **Node.js** (versão 18 ou superior) e **NPM**
- **MySQL** 5.7+ ou **MariaDB** 10.3+ (recomendado) ou **PostgreSQL** (alternativa)
- **Git** (opcional, para controle de versão)

## 🚀 Instalação Passo a Passo

### 1. Clonar o Repositório

```bash
git clone [url-do-repositorio] road-master
cd road-master
```

### 2. Instalar Dependências PHP

```bash
composer install
```

### 3. Configurar Ambiente

Copie o arquivo de exemplo de configuração:

```bash
cp .env.example .env
```

Edite o arquivo `.env` e configure:

```env
APP_NAME="Road Master"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Banco de Dados (MySQL é o padrão)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=road_master
DB_USERNAME=root
DB_PASSWORD=

# Para usar SQLite (desenvolvimento local)
# DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite

# Queue (para processamento em background)
QUEUE_CONNECTION=database
```

### 4. Gerar Chave de Aplicação

```bash
php artisan key:generate
```

### 5. Configurar Banco de Dados

#### Opção A: MySQL (Recomendado)

1. Crie um banco de dados:
```sql
CREATE DATABASE road_master CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Configure o `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=road_master
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

#### Opção B: SQLite (Desenvolvimento Local)

```bash
# Criar arquivo do banco (se não existir)
touch database/database.sqlite
```

No arquivo `.env`, configure:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
# Comente ou remova DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD
```

#### Opção C: PostgreSQL (Alternativa)

1. Crie um banco de dados:
```sql
CREATE DATABASE road_master;
```

2. Configure o `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=road_master
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 6. Executar Migrações e Seeders

```bash
php artisan migrate --seed
```

Isso irá:
- Criar todas as tabelas do banco de dados
- Popular dados iniciais (tipos de combustível, métodos de pagamento, etc.)
- Criar o usuário administrador padrão

### 7. Criar Link Simbólico do Storage

```bash
php artisan storage:link
```

### 8. Instalar Dependências Node.js

```bash
npm install
```

### 9. Compilar Assets

#### Desenvolvimento (com hot reload):
```bash
npm run dev
```

#### Produção:
```bash
npm run build
```

### 10. Iniciar o Servidor

```bash
php artisan serve
```

Acesse: `http://localhost:8000`

## 🔧 Configuração Adicional

### Configurar Queue Worker (Importações em Background)

Para que as importações funcionem corretamente, é necessário executar o queue worker:

```bash
php artisan queue:work
```

Ou em desenvolvimento, use o script do composer:

```bash
composer dev
```

Isso iniciará automaticamente:
- Servidor Laravel
- Queue worker
- Vite dev server
- Laravel Pail (logs)

### Configurar Cron (Produção)

Para que as notificações de revisão funcionem automaticamente, configure o Cron no servidor:

```bash
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

Isso executará o scheduler do Laravel a cada minuto, que por sua vez executará os comandos agendados (como verificação de notificações de revisão às 8h).

### Configurar Permissões (Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## 👤 Usuário Padrão

Após a instalação, você pode fazer login com:

- **Email**: admin@roadmaster.com
- **Senha**: admin123

⚠️ **IMPORTANTE**: Altere a senha padrão imediatamente após o primeiro acesso!

## 🧪 Verificar Instalação

Execute os testes para verificar se tudo está funcionando:

```bash
php artisan test
```

## 🐛 Solução de Problemas

### Erro: "SQLSTATE[HY000] [2002] No connection could be made"

**Solução**: Verifique se o MySQL está rodando e se as credenciais no `.env` estão corretas.

```bash
# Verificar se MySQL está rodando
# Windows
net start MySQL

# Linux
sudo systemctl status mysql

# macOS
brew services list
```

### Erro: "SQLSTATE[42000] [1049] Unknown database"

**Solução**: Crie o banco de dados antes de executar as migrações:

```sql
CREATE DATABASE road_master CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Erro: "Class 'PDO' not found"

**Solução**: Instale a extensão PDO do MySQL:

```bash
# Ubuntu/Debian
sudo apt-get install php-pdo php-mysql

# macOS (Homebrew)
brew install php@8.2

# Windows (via XAMPP/WAMP)
# Ative a extensão php_pdo_mysql.dll no php.ini
```

### Erro: "Vite manifest not found"

**Solução**: Compile os assets:

```bash
npm run build
```

### Erro: "Queue connection not found"

**Solução**: Certifique-se de que o `.env` tem:

```env
QUEUE_CONNECTION=database
```

E execute as migrações:

```bash
php artisan migrate
```

### Erro: "Storage link not found"

**Solução**: Crie o link simbólico:

```bash
php artisan storage:link
```

## 📦 Scripts Disponíveis

### Composer Scripts

```bash
# Setup completo (instalação inicial)
composer setup

# Desenvolvimento (servidor + queue + vite + logs)
composer dev

# Executar testes
composer test
```

### NPM Scripts

```bash
# Desenvolvimento (com hot reload)
npm run dev

# Compilar para produção
npm run build
```

### Artisan Commands

```bash
# Verificar notificações de revisão manualmente
php artisan reviews:check

# Listar comandos agendados
php artisan schedule:list

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Otimizar aplicação (produção)
php artisan optimize
```

## 🌐 Configuração para Produção

### 1. Ambiente

No arquivo `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com
```

### 2. Otimizar Aplicação

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 3. Configurar Servidor Web

#### Apache

Certifique-se de que o `.htaccess` está na pasta `public/` e configure o VirtualHost:

```apache
<VirtualHost *:80>
    ServerName seu-dominio.com
    DocumentRoot /caminho/do/projeto/public
    
    <Directory /caminho/do/projeto/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name seu-dominio.com;
    root /caminho/do/projeto/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4. Configurar Supervisor (Queue Worker)

Crie o arquivo `/etc/supervisor/conf.d/road-master-worker.conf`:

```ini
[program:road-master-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /caminho/do/projeto/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/caminho/do/projeto/storage/logs/worker.log
stopwaitsecs=3600
```

Recarregue o Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start road-master-worker:*
```

### 5. Configurar Cron

Edite o crontab:

```bash
crontab -e
```

Adicione:

```bash
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

## ✅ Checklist de Instalação

- [ ] PHP 8.2+ instalado
- [ ] Composer instalado
- [ ] Node.js e NPM instalados
- [ ] Banco de dados configurado
- [ ] Arquivo `.env` configurado
- [ ] Chave de aplicação gerada
- [ ] Migrações executadas
- [ ] Seeders executados
- [ ] Link simbólico do storage criado
- [ ] Assets compilados
- [ ] Queue worker configurado (produção)
- [ ] Cron configurado (produção)
- [ ] Permissões de arquivos configuradas
- [ ] Servidor web configurado
- [ ] Testes executados com sucesso

## 📚 Próximos Passos

Após a instalação:

1. Faça login com o usuário administrador
2. Altere a senha padrão
3. Configure os tipos de combustível, métodos de pagamento, etc.
4. Cadastre seus veículos
5. Cadastre locais (postos, oficinas, etc.)
6. Configure notificações de revisão
7. Comece a registrar percursos e abastecimentos

Consulte a [documentação dos módulos](MODULOS.md) para mais detalhes sobre como usar cada funcionalidade.

