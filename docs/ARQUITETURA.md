# Arquitetura do Sistema

## 📐 Visão Geral

O SCKV é uma aplicação web desenvolvida seguindo o padrão **MVC (Model-View-Controller)** do Laravel, com arquitetura modular e separação clara de responsabilidades.

## 🏗️ Estrutura de Diretórios

```
sckv/
├── app/                          # Código da aplicação
│   ├── Console/
│   │   └── Commands/            # Comandos Artisan personalizados
│   ├── Http/
│   │   ├── Controllers/         # Controllers (lógica de negócio)
│   │   └── Requests/            # Form Requests (validação)
│   ├── Imports/                 # Classes de importação Excel
│   ├── Jobs/                    # Jobs para processamento em background
│   ├── Models/                  # Models Eloquent (entidades)
│   ├── Policies/                # Políticas de autorização
│   ├── Providers/              # Service Providers
│   └── View/
│       └── Components/         # Componentes Blade reutilizáveis
├── bootstrap/                   # Arquivos de inicialização
├── config/                      # Arquivos de configuração
├── database/
│   ├── migrations/              # Migrations do banco de dados
│   ├── seeders/                # Seeders para dados iniciais
│   └── factories/              # Factories para testes
├── public/                     # Arquivos públicos (ponto de entrada)
├── resources/
│   ├── css/                    # Estilos CSS
│   ├── js/                     # JavaScript
│   └── views/                  # Views Blade
├── routes/                     # Definição de rotas
├── storage/                    # Arquivos de armazenamento
└── tests/                      # Testes automatizados
```

## 🔄 Fluxo de Requisição

```
1. Requisição HTTP
   ↓
2. public/index.php (Ponto de entrada)
   ↓
3. Bootstrap da aplicação (bootstrap/app.php)
   ↓
4. Middleware (autenticação, CSRF, etc.)
   ↓
5. Router (routes/web.php)
   ↓
6. Controller (app/Http/Controllers/)
   ↓
7. Model (app/Models/) - Acesso ao banco de dados
   ↓
8. View (resources/views/) - Renderização
   ↓
9. Resposta HTTP
```

## 📦 Camadas da Aplicação

### 1. Camada de Apresentação (Views)

**Localização**: `resources/views/`

- **Tecnologia**: Blade Templates
- **Estilização**: TailwindCSS
- **Interatividade**: Alpine.js
- **Estrutura**: Layouts, componentes e views específicas

**Componentes Principais**:
- `layouts/app.blade.php` - Layout principal
- `layouts/navigation.blade.php` - Menu de navegação
- `components/` - Componentes reutilizáveis

### 2. Camada de Controle (Controllers)

**Localização**: `app/Http/Controllers/`

Responsabilidades:
- Receber requisições HTTP
- Validar dados (via Form Requests)
- Chamar Models para operações no banco
- Retornar views ou respostas JSON

**Controllers Principais**:
- `DashboardController` - Dashboard principal
- `VehicleController` - CRUD de veículos
- `TripController` - CRUD de percursos
- `FuelingController` - CRUD de abastecimentos
- `MaintenanceController` - CRUD de manutenções
- `ImportController` - Importação de dados
- `ReportController` - Relatórios
- `ReviewNotificationController` - Notificações de revisão

### 3. Camada de Modelo (Models)

**Localização**: `app/Models/`

Responsabilidades:
- Representar entidades do banco de dados
- Definir relacionamentos
- Lógica de negócio específica da entidade
- Scopes e métodos auxiliares

**Models Principais**:
- `User` - Usuários do sistema
- `Vehicle` - Veículos
- `Trip` - Percursos
- `Fueling` - Abastecimentos
- `Maintenance` - Manutenções
- `Location` - Locais
- `ReviewNotification` - Notificações de revisão

### 4. Camada de Dados (Database)

**Localização**: `database/`

**Migrations**: Definem a estrutura do banco de dados
**Seeders**: Populam dados iniciais
**Factories**: Geram dados de teste

## 🔐 Sistema de Autenticação e Autorização

### Autenticação

- **Framework**: Laravel Breeze
- **Método**: Session-based authentication
- **Middleware**: `auth`, `verified`

### Autorização

- **Policies**: `app/Policies/`
  - `VehiclePolicy`
  - `TripPolicy`
  - `FuelingPolicy`
  - `MaintenancePolicy`
  - `UserPolicy`
  - `ReviewNotificationPolicy`
  - `SettingsPolicy`

- **Sistema de Permissões**:
  - Roles: `admin`, `condutor`
  - Permissões por módulo: `can_view`, `can_create`, `can_edit`, `can_delete`
  - Tabela: `user_module_permissions`

## 📊 Padrões de Design Utilizados

### 1. Repository Pattern (Parcial)

Os Models Eloquent atuam como repositories, encapsulando a lógica de acesso aos dados.

### 2. Service Layer (Parcial)

Algumas operações complexas são encapsuladas em métodos dos Models ou em Jobs.

### 3. Observer Pattern

Eventos do Laravel são utilizados para ações automáticas (ex: atualização de odômetro).

### 4. Queue Pattern

Processamento assíncrono para:
- Importação de dados Excel
- Processamento de notificações

### 5. Strategy Pattern

Diferentes estratégias de importação (KMImport, SheetTripsImport).

## 🔄 Processamento em Background

### Jobs

**Localização**: `app/Jobs/`

- `ProcessImportJob` - Processa importações Excel em background

**Configuração**:
- Connection: `database`
- Queue: `default`
- Tries: 3
- Timeout: 3600s

### Comandos Agendados

**Localização**: `bootstrap/app.php`

- `reviews:check` - Verifica notificações de revisão diariamente às 8h

## 📥 Sistema de Importação

### Fluxo de Importação

```
1. Upload do arquivo Excel
   ↓
2. Validação do arquivo
   ↓
3. Criação de job em background
   ↓
4. Processamento assíncrono
   ↓
5. Atualização de progresso via Cache
   ↓
6. Notificação de conclusão
```

### Classes de Importação

- `KMImport` - Importa dados de KM
- `SheetTripsImport` - Importa percursos de uma aba

## 🔔 Sistema de Notificações

### Tipos de Notificação

- `info` - Informações gerais
- `success` - Sucesso
- `warning` - Avisos (ex: revisões)
- `error` - Erros

### Armazenamento

- Tabela: `notifications`
- Relacionamento: `User` hasMany `Notification`

### Exibição

- Ícone de sino no menu (com contador)
- Dropdown com últimas notificações
- Página dedicada (`/notifications`)

## 🎨 Frontend

### Stack

- **CSS Framework**: TailwindCSS 3.x
- **JavaScript Framework**: Alpine.js 3.x
- **Build Tool**: Vite 7.x
- **Package Manager**: NPM

### Estrutura de Assets

```
resources/
├── css/
│   ├── app.css          # Estilos principais
│   └── navigation.css   # Estilos do menu
└── js/
    ├── app.js           # JavaScript principal
    └── bootstrap.js     # Configuração do Alpine.js
```

### Componentes Frontend

- **Alpine.js Components**: Interatividade sem necessidade de framework pesado
- **Blade Components**: Componentes reutilizáveis
- **TailwindCSS Utilities**: Estilização utilitária

## 🗄️ Banco de Dados

### Estrutura

- **ORM**: Eloquent ORM
- **Migrations**: Versionamento do schema
- **Seeders**: Dados iniciais

### Relacionamentos Principais

```
User
  ├── hasMany Trip (driver)
  ├── hasMany Fueling
  ├── belongsToMany Vehicle
  └── hasMany Notification

Vehicle
  ├── hasMany Trip
  ├── hasMany Fueling
  ├── hasMany Maintenance
  ├── hasMany ReviewNotification
  ├── belongsToMany User
  └── belongsToMany FuelType

Trip
  ├── belongsTo Vehicle
  ├── belongsTo User (driver)
  ├── belongsTo Location (origin)
  ├── belongsTo Location (destination)
  └── hasMany TripStop

Fueling
  ├── belongsTo Vehicle
  ├── belongsTo User
  └── belongsTo PaymentMethod

Maintenance
  ├── belongsTo Vehicle
  └── belongsTo MaintenanceType
```

## 🔧 Configurações

### Arquivos de Configuração

- `config/app.php` - Configurações gerais
- `config/database.php` - Configurações de banco
- `config/queue.php` - Configurações de fila
- `config/filesystems.php` - Configurações de armazenamento

### Variáveis de Ambiente

Principais variáveis no `.env`:
- `APP_ENV` - Ambiente (local, production)
- `APP_DEBUG` - Modo debug
- `DB_CONNECTION` - Tipo de banco
- `QUEUE_CONNECTION` - Tipo de fila
- `MAIL_*` - Configurações de email

## 🧪 Testes

### Estrutura

- **Framework**: PHPUnit
- **Localização**: `tests/`
- **Tipos**: Feature Tests, Unit Tests

### Executar Testes

```bash
php artisan test
```

## 📈 Performance

### Otimizações

- **Cache**: Config, routes, views
- **Eager Loading**: Prevenção de N+1 queries
- **Queue**: Processamento assíncrono
- **Database Indexes**: Índices em campos frequentemente consultados

### Monitoramento

- Logs: `storage/logs/laravel.log`
- Laravel Pail: Visualização de logs em tempo real

## 🔒 Segurança

### Medidas Implementadas

- **CSRF Protection**: Tokens em todos os formulários
- **XSS Protection**: Escaping automático no Blade
- **SQL Injection**: Protegido pelo Eloquent ORM
- **Authorization**: Policies e middleware
- **Password Hashing**: Bcrypt
- **Input Validation**: Form Requests

## 🚀 Deploy

### Processo de Deploy

1. Atualizar código
2. Instalar dependências (`composer install --no-dev`)
3. Executar migrações (`php artisan migrate --force`)
4. Compilar assets (`npm run build`)
5. Otimizar aplicação (`php artisan optimize`)
6. Reiniciar workers e scheduler

### Requisitos de Servidor

- PHP 8.2+
- Extensões PHP necessárias
- Composer
- Node.js (apenas para build)
- Supervisor (para queue workers)
- Cron (para scheduled commands)

## 📚 Bibliotecas e Dependências Principais

### Backend

- `laravel/framework` - Framework principal
- `maatwebsite/excel` - Importação/exportação Excel
- `laravel/breeze` - Autenticação

### Frontend

- `tailwindcss` - Framework CSS
- `alpinejs` - Framework JavaScript
- `vite` - Build tool

## 🔄 Fluxo de Dados

### Criação de Percurso

```
1. Usuário preenche formulário
   ↓
2. TripController@store recebe Request
   ↓
3. Validação via Form Request
   ↓
4. TripController cria Trip via Model
   ↓
5. Model atualiza odômetro do Vehicle
   ↓
6. Retorna view com mensagem de sucesso
```

### Importação de Dados

```
1. Usuário faz upload de Excel
   ↓
2. ImportController valida arquivo
   ↓
3. Cria ProcessImportJob
   ↓
4. Job processa em background
   ↓
5. Atualiza progresso via Cache
   ↓
6. Frontend consulta progresso via AJAX
   ↓
7. Notifica conclusão
```

## 🎯 Princípios de Design

1. **Separation of Concerns**: Cada camada tem responsabilidade específica
2. **DRY (Don't Repeat Yourself)**: Reutilização de código
3. **SOLID**: Princípios de design orientado a objetos
4. **RESTful**: Rotas seguem padrão REST
5. **Convention over Configuration**: Convenções do Laravel

## 📝 Convenções de Código

- **PSR-12**: Padrão de codificação PHP
- **Laravel Conventions**: Convenções do framework
- **Naming**: camelCase para métodos, PascalCase para classes
- **Controllers**: Nome no singular + Controller
- **Models**: Nome no singular
- **Migrations**: Nome descritivo com timestamp

---

Esta arquitetura foi projetada para ser escalável, manutenível e seguir as melhores práticas do Laravel e desenvolvimento web moderno.

