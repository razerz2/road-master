# Road Master - Sistema de Controle de KM e Veículos

## 📋 Sobre o Projeto

O **Road Master** é uma aplicação web desenvolvida em Laravel para gerenciamento completo de frotas de veículos. O sistema permite controlar quilometragem, abastecimentos, manutenções, percursos e notificações de revisão de forma centralizada e eficiente.

## 🚀 Início Rápido

### Pré-requisitos

- PHP 8.2 ou superior
- Composer
- Node.js e NPM
- MySQL 5.7+ ou MariaDB 10.3+ (recomendado)

### Instalação Rápida

```bash
# Clonar o repositório
git clone [url-do-repositorio] road-master
cd road-master

# Instalar dependências
composer install
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

Acesse: `http://localhost:8000`

## 👤 Usuário Padrão

Após a instalação, você pode fazer login com:

- **Email**: admin@roadmaster.com
- **Senha**: admin123

⚠️ **Importante**: Altere a senha padrão após o primeiro acesso!

## 📚 Documentação Completa

Toda a documentação do projeto está disponível na pasta `docs/`:

- **[README Principal](docs/README.md)** - Visão geral completa do projeto
- **[Instalação e Configuração](docs/INSTALACAO.md)** - Guia detalhado de instalação
- **[Arquitetura do Sistema](docs/ARQUITETURA.md)** - Estrutura e organização do código
- **[Módulos do Sistema](docs/MODULOS.md)** - Documentação de cada módulo
- **[Banco de Dados](docs/BANCO_DADOS.md)** - Estrutura do banco de dados
- **[Rotas e API](docs/ROTAS_API.md)** - Documentação de todas as rotas
- **[Guia de Desenvolvimento](docs/DESENVOLVIMENTO.md)** - Guia para desenvolvedores
- **[Notificações de Revisão](docs/REVIEW_NOTIFICATIONS.md)** - Documentação do módulo
- **[Como Funcionam as Notificações](docs/COMO_FUNCIONA_NOTIFICACOES_REVISAO.md)** - Detalhamento técnico

## 🎯 Funcionalidades Principais

- 🚗 **Gestão de Veículos** - Cadastro completo de veículos da frota
- 🗺️ **Gestão de Locais** - Cadastro de origens, destinos, postos, etc.
- 🛣️ **Gestão de Percursos** - Registro de viagens com controle de KM
- ⛽ **Gestão de Abastecimentos** - Controle de abastecimentos e custos
- 🔧 **Gestão de Manutenções** - Registro e planejamento de manutenções
- 🔔 **Notificações de Revisão** - Notificações automáticas baseadas em KM
- 📋 **Obrigações Legais** - Controle de IPVA, Licenciamento e Multas
- 🏪 **Postos de Combustível** - Cadastro e gestão de postos
- 📊 **Relatórios** - Mais de 15 relatórios com exportação Excel/PDF
- 📥 **Importação** - Importação em massa via Excel
- 👥 **Gestão de Usuários** - Sistema de permissões e roles
- ⚙️ **Configurações** - Configurações gerais e personalização

## 🛠️ Tecnologias

- **Backend**: Laravel 12.x
- **Frontend**: Blade Templates, TailwindCSS 3.x, Alpine.js 3.x
- **Banco de Dados**: SQLite (desenvolvimento) / MySQL / PostgreSQL
- **Processamento**: Laravel Queue (database driver)
- **Importação/Exportação**: Maatwebsite Excel
- **Geração de PDF**: DomPDF
- **Build Tool**: Vite 7.x
- **PHP**: 8.2+

## 📦 Scripts Disponíveis

```bash
# Setup completo (instalação inicial)
composer setup

# Desenvolvimento (servidor + queue + vite + logs)
composer dev

# Executar testes
composer test

# Compilar assets para produção
npm run build
```

## 🔐 Segurança

- Autenticação via Laravel Breeze
- Autorização baseada em Policies
- Proteção CSRF
- Validação de dados
- Sanitização de inputs

## 📝 Licença

Este projeto é de código aberto e está disponível sob a licença MIT.

## 🤝 Contribuindo

Contribuições são bem-vindas! Consulte o [guia de desenvolvimento](docs/DESENVOLVIMENTO.md) antes de contribuir.

## 📞 Suporte

Para dúvidas ou problemas, consulte a [documentação completa](docs/README.md) ou abra uma issue no repositório.

---

**Desenvolvido com ❤️ usando Laravel**
