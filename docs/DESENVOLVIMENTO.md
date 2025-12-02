# Guia de Desenvolvimento

## 📋 Visão Geral

Este guia fornece informações para desenvolvedores que desejam contribuir ou estender o sistema Road Master.

## 🛠️ Ambiente de Desenvolvimento

### Requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- SQLite (desenvolvimento) ou MySQL/PostgreSQL
- Git

### Configuração Inicial

```bash
# Clonar repositório
git clone [url-do-repositorio] road-master
cd road-master

# Instalar dependências
composer install
npm install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Configurar banco de dados
touch database/database.sqlite
# Ou configure MySQL/PostgreSQL no .env

# Executar migrações
php artisan migrate --seed

# Compilar assets
npm run build

# Iniciar servidor de desenvolvimento
composer dev
```

## 📁 Estrutura do Código

### Convenções

- **PSR-12**: Padrão de codificação PHP
- **Laravel Conventions**: Seguir convenções do framework
- **Naming**: 
  - Classes: PascalCase
  - Métodos/Variáveis: camelCase
  - Constantes: UPPER_SNAKE_CASE
  - Arquivos: snake_case.php

### Organização

```
app/
├── Console/Commands/     # Comandos Artisan
├── Http/
│   ├── Controllers/      # Controllers
│   └── Requests/         # Form Requests (validação)
├── Imports/              # Classes de importação
├── Jobs/                  # Jobs para fila
├── Models/                # Models Eloquent
├── Policies/              # Políticas de autorização
└── Providers/             # Service Providers
```

## 🔧 Desenvolvimento

### Criar um Novo Módulo

1. **Criar Migration**

```bash
php artisan make:migration create_example_table
```

2. **Criar Model**

```bash
php artisan make:model Example
```

3. **Criar Controller**

```bash
php artisan make:controller ExampleController --resource
```

4. **Criar Policy (se necessário)**

```bash
php artisan make:policy ExamplePolicy --model=Example
```

5. **Criar Views**

```bash
mkdir resources/views/examples
# Criar: index.blade.php, create.blade.php, edit.blade.php, show.blade.php
```

6. **Definir Rotas**

Em `routes/web.php`:

```php
Route::resource('examples', ExampleController::class);
```

7. **Criar Seeder (opcional)**

```bash
php artisan make:seeder ExampleSeeder
```

### Adicionar Validação

Criar Form Request:

```bash
php artisan make:request StoreExampleRequest
```

Exemplo:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ou verificar permissões
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ];
    }
}
```

Usar no Controller:

```php
public function store(StoreExampleRequest $request)
{
    // Dados já validados
    $data = $request->validated();
    // ...
}
```

### Trabalhar com Models

#### Definir Relacionamentos

```php
// Model Example
public function relatedModel()
{
    return $this->belongsTo(RelatedModel::class);
}

public function manyRelated()
{
    return $this->hasMany(ManyRelated::class);
}

public function manyToMany()
{
    return $this->belongsToMany(OtherModel::class);
}
```

#### Usar Scopes

```php
// No Model
public function scopeActive($query)
{
    return $query->where('active', true);
}

// Uso
$examples = Example::active()->get();
```

#### Eager Loading

```php
// Evitar N+1 queries
$examples = Example::with('relatedModel')->get();
```

### Trabalhar com Views

#### Blade Components

Criar componente:

```bash
php artisan make:component ExampleCard
```

Usar:

```blade
<x-example-card :example="$example" />
```

#### Layouts

Usar layout principal:

```blade
@extends('layouts.app')

@section('content')
    <!-- Conteúdo -->
@endsection
```

### Trabalhar com Jobs

Criar Job:

```bash
php artisan make:job ProcessExample
```

Exemplo:

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessExample implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public $data
    ) {}

    public function handle(): void
    {
        // Processar dados
    }
}
```

Disparar:

```php
ProcessExample::dispatch($data);
```

### Trabalhar com Comandos

Criar comando:

```bash
php artisan make:command ExampleCommand
```

Exemplo:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExampleCommand extends Command
{
    protected $signature = 'example:process';
    protected $description = 'Processa exemplo';

    public function handle(): int
    {
        $this->info('Processando...');
        // Lógica
        $this->info('Concluído!');
        return Command::SUCCESS;
    }
}
```

Agendar em `bootstrap/app.php`:

```php
->withSchedule(function (Schedule $schedule): void {
    $schedule->command('example:process')->daily();
})
```

## 🧪 Testes

### Estrutura

```
tests/
├── Feature/          # Testes de integração
└── Unit/             # Testes unitários
```

### Escrever Testes

Exemplo de teste Feature:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_example_creation(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
            ->post('/examples', [
                'name' => 'Test Example',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('examples', [
            'name' => 'Test Example',
        ]);
    }
}
```

### Executar Testes

```bash
# Todos os testes
php artisan test

# Teste específico
php artisan test --filter ExampleTest

# Com cobertura
php artisan test --coverage
```

## 🔍 Debugging

### Logs

```php
// No código
\Log::info('Mensagem', ['data' => $data]);
\Log::error('Erro', ['exception' => $e]);
```

Visualizar logs:

```bash
# Laravel Pail (tempo real)
php artisan pail

# Ou tail
tail -f storage/logs/laravel.log
```

### Debug Bar

O Laravel Debugbar é útil para desenvolvimento (não incluir em produção).

### Tinker

```bash
php artisan tinker

# Exemplos
$user = User::first();
$vehicle = Vehicle::with('trips')->first();
```

## 📦 Dependências

### Adicionar Pacote PHP

```bash
composer require vendor/package
```

### Adicionar Pacote NPM

```bash
npm install package-name
```

### Atualizar Dependências

```bash
composer update
npm update
```

## 🗄️ Migrations

### Criar Migration

```bash
php artisan make:migration add_field_to_table
```

### Executar Migrations

```bash
# Executar todas
php artisan migrate

# Rollback última
php artisan migrate:rollback

# Rollback todas
php artisan migrate:reset

# Refresh (rollback + migrate)
php artisan migrate:refresh

# Refresh com seeders
php artisan migrate:refresh --seed
```

### Boas Práticas

- Sempre criar migrations para mudanças no banco
- Não modificar migrations já executadas em produção
- Criar nova migration para alterações

## 🎨 Frontend

### TailwindCSS

Adicionar classes:

```blade
<div class="bg-blue-500 text-white p-4 rounded">
    Conteúdo
</div>
```

### Alpine.js

```blade
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Conteúdo</div>
</div>
```

### Compilar Assets

```bash
# Desenvolvimento (hot reload)
npm run dev

# Produção
npm run build
```

## 🔐 Segurança

### Validação

Sempre validar inputs:

```php
$request->validate([
    'email' => 'required|email',
    'password' => 'required|min:8',
]);
```

### Autorização

Usar Policies:

```php
$this->authorize('update', $vehicle);
```

### CSRF

Todos os formulários devem incluir:

```blade
@csrf
```

### SQL Injection

Usar Query Builder ou Eloquent (nunca SQL direto):

```php
// ✅ Correto
User::where('email', $email)->first();

// ❌ Errado
DB::select("SELECT * FROM users WHERE email = '$email'");
```

## 📝 Documentação de Código

### PHPDoc

```php
/**
 * Calcula o total de KM rodado.
 *
 * @param int $vehicleId ID do veículo
 * @param string $startDate Data inicial
 * @param string $endDate Data final
 * @return int Total de KM
 */
public function calculateTotalKm(int $vehicleId, string $startDate, string $endDate): int
{
    // ...
}
```

### Comentários

- Comentar código complexo
- Explicar "por quê", não "o quê"
- Manter comentários atualizados

## 🚀 Deploy

### Checklist

- [ ] Executar testes
- [ ] Atualizar dependências (`composer install --no-dev`)
- [ ] Executar migrações
- [ ] Compilar assets (`npm run build`)
- [ ] Otimizar aplicação (`php artisan optimize`)
- [ ] Configurar queue worker
- [ ] Configurar cron
- [ ] Verificar permissões de arquivos
- [ ] Configurar variáveis de ambiente

### Comandos de Produção

```bash
# Otimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Limpar cache (se necessário)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 🤝 Contribuindo

### Workflow

1. Criar branch: `git checkout -b feature/nova-funcionalidade`
2. Fazer alterações
3. Commitar: `git commit -m "Adiciona nova funcionalidade"`
4. Push: `git push origin feature/nova-funcionalidade`
5. Criar Pull Request

### Padrões de Commit

```
feat: Adiciona nova funcionalidade
fix: Corrige bug
docs: Atualiza documentação
style: Ajusta formatação
refactor: Refatora código
test: Adiciona testes
chore: Tarefas de manutenção
```

### Code Review

- Revisar código antes de merge
- Verificar testes
- Verificar documentação
- Verificar padrões de código

## 📚 Recursos

### Documentação Laravel

- [Laravel Docs](https://laravel.com/docs)
- [Laravel API](https://laravel.com/api)

### Ferramentas

- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar)
- [Laravel Telescope](https://laravel.com/docs/telescope)
- [Laravel IDE Helper](https://github.com/barryvdh/laravel-ide-helper)

### Comunidade

- [Laravel Brasil](https://laravel.com.br)
- [Laracasts](https://laracasts.com)

---

Este guia cobre os principais aspectos do desenvolvimento. Para mais detalhes, consulte a [documentação de arquitetura](ARQUITETURA.md) e a [documentação dos módulos](MODULOS.md).

