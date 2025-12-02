# Road Master - Sistema de Controle de KM e Veículos

## 📋 Sobre o Projeto

O **Road Master** é uma aplicação web desenvolvida em Laravel para gerenciamento completo de frotas de veículos. O sistema permite controlar quilometragem, abastecimentos, manutenções, percursos e notificações de revisão de forma centralizada e eficiente.

## 🎯 Funcionalidades Principais

### 🚗 Gestão de Veículos
- Cadastro completo de veículos (marca, modelo, ano, placa, etc.)
- Controle de odômetro atual e inicial
- Gestão de capacidade de tanque
- Controle de tipos de combustível por veículo
- Vinculação de usuários (condutores) aos veículos
- Ativação/desativação de veículos

### 🗺️ Gestão de Locais
- Cadastro de locais (origens e destinos)
- Tipos de local (posto, oficina, cliente, etc.)
- Endereços completos com CEP, cidade, estado
- Busca e filtros avançados

### 🛣️ Gestão de Percursos (Trips)
- Registro de viagens com origem e destino
- Controle de odômetro inicial e final
- Cálculo automático de KM rodado
- Múltiplas paradas intermediárias
- Vinculação com condutor e veículo
- Histórico completo de percursos

### ⛽ Gestão de Abastecimentos
- Registro de abastecimentos com data/hora
- Controle de odômetro no momento do abastecimento
- Múltiplos tipos de combustível
- Métodos de pagamento
- Cálculo automático de custos
- Histórico completo por veículo

### 🔧 Gestão de Manutenções
- Registro de manutenções realizadas
- Tipos de manutenção (preventiva, corretiva, etc.)
- Controle de custos
- Próxima manutenção prevista (data e KM)
- Histórico completo por veículo

### 🔔 Notificações de Revisão
- Configuração de notificações automáticas por KM
- Múltiplos tipos de revisão (troca de óleo, manutenção, etc.)
- Verificação automática diária
- Notificações para usuários relacionados
- Prevenção de notificações duplicadas

### 📊 Relatórios
- KM rodado por veículo
- Custo de combustível por veículo
- Histórico de manutenções
- Filtros por período e veículo

### 📥 Importação de Dados
- Importação em massa de percursos via Excel
- Processamento em background (queue)
- Acompanhamento de progresso em tempo real
- Suporte a múltiplas abas no arquivo Excel

### 👥 Gestão de Usuários e Permissões
- Sistema de roles (admin, condutor)
- Permissões granulares por módulo
- Controle de acesso baseado em permissões
- Vinculação de condutores a veículos

### ⚙️ Configurações
- Configurações gerais do sistema
- Personalização de aparência
- Preferências de dashboard
- Tipos de combustível, manutenção, local e métodos de pagamento

## 🛠️ Tecnologias Utilizadas

- **Backend**: Laravel 12.x
- **Frontend**: Blade Templates, TailwindCSS, Alpine.js
- **Banco de Dados**: SQLite (desenvolvimento) / MySQL/PostgreSQL (produção)
- **Processamento**: Laravel Queue (jobs em background)
- **Importação**: Maatwebsite Excel
- **PHP**: 8.2+

## 📚 Documentação

A documentação completa está organizada nos seguintes arquivos:

- **[Instalação e Configuração](INSTALACAO.md)** - Guia completo de instalação e configuração do sistema
- **[Arquitetura do Sistema](ARQUITETURA.md)** - Estrutura e organização do código
- **[Módulos do Sistema](MODULOS.md)** - Documentação detalhada de cada módulo
- **[Banco de Dados](BANCO_DADOS.md)** - Estrutura do banco de dados e relacionamentos
- **[Rotas e API](ROTAS_API.md)** - Documentação de todas as rotas do sistema
- **[Guia de Desenvolvimento](DESENVOLVIMENTO.md)** - Guia para desenvolvedores
- **[Notificações de Revisão](REVIEW_NOTIFICATIONS.md)** - Documentação do módulo de notificações
- **[Como Funcionam as Notificações](COMO_FUNCIONA_NOTIFICACOES_REVISAO.md)** - Detalhamento técnico do sistema de notificações

## 🚀 Início Rápido

### Pré-requisitos

- PHP 8.2 ou superior
- Composer
- Node.js e NPM
- SQLite (desenvolvimento) ou MySQL/PostgreSQL (produção)

### Instalação

```bash
# Clonar o repositório
git clone [url-do-repositorio]

# Entrar no diretório
cd road-master

# Instalar dependências PHP
composer install

# Instalar dependências Node
npm install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Executar migrações e seeders
php artisan migrate --seed

# Compilar assets
npm run build

# Iniciar servidor
php artisan serve
```

Para mais detalhes, consulte a [documentação de instalação](INSTALACAO.md).

## 👤 Usuário Padrão

Após a instalação, você pode fazer login com:

- **Email**: admin@roadmaster.com
- **Senha**: admin123

⚠️ **Importante**: Altere a senha padrão após o primeiro acesso!

## 📁 Estrutura do Projeto

```
road-master/
├── app/
│   ├── Console/Commands/     # Comandos Artisan
│   ├── Http/Controllers/      # Controllers
│   ├── Imports/              # Classes de importação Excel
│   ├── Jobs/                  # Jobs para processamento em background
│   ├── Models/                # Models Eloquent
│   ├── Policies/              # Políticas de autorização
│   └── Providers/             # Service Providers
├── database/
│   ├── migrations/            # Migrations do banco
│   └── seeders/               # Seeders para dados iniciais
├── docs/                      # Documentação do projeto
├── public/                    # Arquivos públicos
├── resources/
│   ├── css/                   # Estilos CSS
│   ├── js/                    # JavaScript
│   └── views/                 # Views Blade
├── routes/                    # Rotas da aplicação
└── tests/                     # Testes automatizados
```

## 🔐 Segurança

- Autenticação via Laravel Breeze
- Autorização baseada em Policies
- Proteção CSRF em todos os formulários
- Validação de dados em todos os inputs
- Sanitização de dados de entrada

## 📝 Licença

Este projeto é de código aberto e está disponível sob a licença MIT.

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor, leia o [guia de desenvolvimento](DESENVOLVIMENTO.md) antes de contribuir.

## 📞 Suporte

Para dúvidas ou problemas, consulte a documentação ou abra uma issue no repositório.

---

**Desenvolvido com ❤️ usando Laravel**

