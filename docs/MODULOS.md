# Documentação dos Módulos

Este documento descreve em detalhes cada módulo do sistema Road Master.

## 📑 Índice

1. [Dashboard](#dashboard)
2. [Veículos](#veículos)
3. [Locais](#locais)
4. [Percursos](#percursos)
5. [Abastecimentos](#abastecimentos)
6. [Manutenções](#manutenções)
7. [Notificações de Revisão](#notificações-de-revisão)
8. [Obrigações Legais](#obrigações-legais)
9. [Postos de Combustível](#postos-de-combustível)
10. [Relatórios](#relatórios)
11. [Importação](#importação)
12. [Usuários](#usuários)
13. [Configurações](#configurações)
14. [Notificações](#notificações)

---

## 🏠 Dashboard

**Rota**: `/dashboard`  
**Controller**: `DashboardController`  
**View**: `resources/views/dashboard.blade.php`

### Funcionalidades

O dashboard fornece uma visão geral do sistema com métricas e estatísticas.

### Métricas Exibidas

- **Total de Veículos Ativos**: Contagem de veículos ativos no sistema
- **KM Total Rodado**: Soma de todos os KM rodados no período selecionado
- **Litros Abastecidos**: Total de litros abastecidos no período
- **Custo de Combustível**: Soma dos custos de abastecimento no período

### Filtros

- **Período**: Data inicial e final (padrão: mês atual)
- **Veículo**: Filtrar por veículo específico (opcional)

### Gráficos/Tabelas

- **KM por Veículo**: Tabela mostrando KM rodado por cada veículo no período

### Permissões

- **Admin**: Vê todos os dados
- **Condutor**: Vê apenas dados dos veículos vinculados

### Preferências do Usuário

As preferências de filtro são salvas e restauradas na próxima visita.

---

## 🚗 Veículos

**Rota Base**: `/vehicles`  
**Controller**: `VehicleController`  
**View**: `resources/views/vehicles/`

### Funcionalidades

Gestão completa de veículos da frota.

### Campos do Cadastro

- **Nome**: Nome identificador do veículo
- **Placa**: Placa do veículo
- **Marca**: Marca do veículo
- **Modelo**: Modelo do veículo
- **Ano**: Ano de fabricação
- **Tipo de Combustível**: Tipo principal de combustível
- **Capacidade do Tanque**: Capacidade em litros
- **KM Inicial**: Quilometragem inicial do veículo
- **Odômetro Atual**: Quilometragem atual (atualizado automaticamente)
- **Ativo**: Status do veículo

### Operações

- **Listar**: Visualizar todos os veículos
- **Criar**: Cadastrar novo veículo
- **Editar**: Atualizar informações
- **Excluir**: Remover veículo (soft delete)
- **Visualizar**: Ver detalhes completos

### Relacionamentos

- **Percursos**: Histórico de viagens
- **Abastecimentos**: Histórico de abastecimentos
- **Manutenções**: Histórico de manutenções
- **Notificações de Revisão**: Notificações configuradas
- **Usuários**: Condutores vinculados
- **Tipos de Combustível**: Combustíveis aceitos

### Atualização Automática de Odômetro

O odômetro é atualizado automaticamente quando:
- Um percurso é criado/editado
- Um abastecimento é registrado

### Permissões

Controladas por `VehiclePolicy`.

---

## 📍 Locais

**Rota Base**: `/locations`  
**Controller**: `LocationController`  
**View**: `resources/views/locations/`

### Funcionalidades

Gestão de locais (origens, destinos, postos, oficinas, etc.).

### Campos do Cadastro

- **Nome**: Nome do local
- **Tipo de Local**: Categoria (posto, oficina, cliente, etc.)
- **Endereço**: Logradouro
- **Número**: Número do endereço
- **Complemento**: Complemento do endereço
- **Bairro**: Bairro
- **Cidade**: Cidade
- **Estado**: Estado (UF)
- **CEP**: Código postal
- **Observações**: Notas adicionais

### Operações

- **Listar**: Visualizar todos os locais
- **Criar**: Cadastrar novo local
- **Editar**: Atualizar informações
- **Excluir**: Remover local
- **Buscar**: Busca por nome, cidade, etc.

### Tipos de Local

Gerenciados em Configurações > Tipos de Local.

### Uso

Locais são utilizados em:
- Percursos (origem e destino)
- Abastecimentos (posto de gasolina)
- Manutenções (oficina)

---

## 🛣️ Percursos

**Rota Base**: `/trips`  
**Controller**: `TripController`  
**View**: `resources/views/trips/`

### Funcionalidades

Registro e gestão de percursos/viagens realizadas.

### Campos do Cadastro

- **Veículo**: Veículo utilizado
- **Condutor**: Usuário que conduziu
- **Data**: Data do percurso
- **Local de Origem**: Ponto de partida
- **Local de Destino**: Ponto de chegada
- **Retornou à Origem**: Se o veículo retornou ao ponto de partida
- **Horário de Saída**: Horário de partida
- **Horário de Retorno**: Horário de retorno (se aplicável)
- **Odômetro Inicial**: KM no início do percurso
- **Odômetro Final**: KM no final do percurso
- **KM Total**: Calculado automaticamente
- **Finalidade**: Motivo do percurso
- **Paradas**: Paradas intermediárias (opcional)

### Operações

- **Listar**: Visualizar todos os percursos
- **Criar**: Registrar novo percurso
- **Editar**: Atualizar informações
- **Excluir**: Remover percurso
- **Visualizar**: Ver detalhes completos

### Paradas Intermediárias

Um percurso pode ter múltiplas paradas:
- Sequência de paradas ordenadas
- Cada parada tem local e observações

### Atualização Automática

Ao criar/editar um percurso:
- Odômetro do veículo é atualizado automaticamente
- KM total é calculado automaticamente

### Permissões

- **Admin**: Vê todos os percursos
- **Condutor**: Vê apenas seus próprios percursos

---

## ⛽ Abastecimentos

**Rota Base**: `/fuelings`  
**Controller**: `FuelingController`  
**View**: `resources/views/fuelings/`

### Funcionalidades

Registro e gestão de abastecimentos realizados.

### Campos do Cadastro

- **Veículo**: Veículo abastecido
- **Usuário**: Usuário que registrou
- **Data/Hora**: Data e hora do abastecimento
- **Odômetro**: KM no momento do abastecimento
- **Tipo de Combustível**: Tipo de combustível
- **Litros**: Quantidade abastecida
- **Preço por Litro**: Valor unitário
- **Valor Total**: Calculado automaticamente
- **Posto**: Nome do posto de gasolina
- **Método de Pagamento**: Forma de pagamento
- **Observações**: Notas adicionais

### Operações

- **Listar**: Visualizar todos os abastecimentos
- **Criar**: Registrar novo abastecimento
- **Editar**: Atualizar informações
- **Excluir**: Remover abastecimento
- **Visualizar**: Ver detalhes completos

### Cálculos Automáticos

- **Valor Total**: `liters × price_per_liter`

### Atualização Automática

Ao criar/editar um abastecimento:
- Odômetro do veículo pode ser atualizado (se for maior que o atual)

### Tipos de Combustível

Gerenciados em Configurações > Tipos de Combustível.

### Métodos de Pagamento

Gerenciados em Configurações > Métodos de Pagamento.

---

## 🔧 Manutenções

**Rota Base**: `/maintenances`  
**Controller**: `MaintenanceController`  
**View**: `resources/views/maintenances/`

### Funcionalidades

Registro e gestão de manutenções realizadas.

### Campos do Cadastro

- **Veículo**: Veículo que recebeu manutenção
- **Data**: Data da manutenção
- **Odômetro**: KM no momento da manutenção
- **Tipo de Manutenção**: Categoria da manutenção
- **Descrição**: Descrição detalhada
- **Fornecedor**: Oficina/fornecedor
- **Custo**: Valor gasto
- **Próxima Data Prevista**: Data da próxima manutenção
- **Próximo KM Previsto**: KM para próxima manutenção
- **Observações**: Notas adicionais

### Operações

- **Listar**: Visualizar todas as manutenções
- **Criar**: Registrar nova manutenção
- **Editar**: Atualizar informações
- **Excluir**: Remover manutenção
- **Visualizar**: Ver detalhes completos

### Tipos de Manutenção

Gerenciados em Configurações > Tipos de Manutenção.

### Planejamento

O sistema permite planejar próximas manutenções:
- Data prevista
- KM previsto

---

## 🔔 Notificações de Revisão

**Rota Base**: `/review-notifications`  
**Controller**: `ReviewNotificationController`  
**View**: `resources/views/review-notifications/`

### Funcionalidades

Configuração de notificações automáticas para revisões baseadas em KM.

### Campos do Cadastro

- **Veículo**: Veículo a ser monitorado
- **Tipo de Revisão**: Tipo de revisão (troca de óleo, etc.)
- **Nome Personalizado**: Nome customizado (opcional)
- **KM Atual**: KM atual do veículo (opcional, usa odômetro se vazio)
- **KM para Notificação**: KM onde a notificação será disparada
- **Descrição**: Informações adicionais
- **Ativo**: Se a notificação está ativa

### Operações

- **Listar**: Visualizar todas as notificações
- **Criar**: Configurar nova notificação
- **Editar**: Atualizar configuração
- **Excluir**: Remover notificação
- **Ativar/Desativar**: Toggle de status

### Tipos de Revisão

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

### Verificação Automática

- Executada diariamente às 8h
- Comando: `php artisan reviews:check`
- Verifica todas as notificações ativas
- Dispara notificações quando KM é atingido

### Prevenção de Duplicatas

- Sistema evita notificações duplicadas
- Usa campo `last_notified_km` para controle

Para mais detalhes, consulte:
- [REVIEW_NOTIFICATIONS.md](REVIEW_NOTIFICATIONS.md)
- [COMO_FUNCIONA_NOTIFICACOES_REVISAO.md](COMO_FUNCIONA_NOTIFICACOES_REVISAO.md)

---

## 📋 Obrigações Legais

**Rota Base**: `/mandatory-events`  
**Controller**: `MandatoryEventController`  
**View**: `resources/views/mandatory-events/`

### Funcionalidades

Gestão de obrigações legais dos veículos (IPVA, Licenciamento, Multas).

### Campos do Cadastro

- **Veículo**: Veículo relacionado
- **Tipo**: Tipo de obrigação (IPVA, Licenciamento, Multa)
- **Descrição**: Descrição da obrigação
- **Data de Vencimento**: Data limite para cumprimento
- **Valor**: Valor da obrigação (opcional)
- **Status**: Status (pendente, resolvido)
- **Observações**: Notas adicionais

### Operações

- **Listar**: Visualizar todas as obrigações
- **Criar**: Cadastrar nova obrigação
- **Editar**: Atualizar informações
- **Excluir**: Remover obrigação
- **Marcar como Resolvido**: Alterar status para resolvido

### Tipos de Obrigações

- **IPVA**: Imposto sobre Propriedade de Veículos Automotores
- **Licenciamento**: Licenciamento anual do veículo
- **Multa**: Multas de trânsito

### Alertas

O sistema pode gerar alertas para obrigações próximas do vencimento (se configurado).

---

## 🏪 Postos de Combustível

**Rota Base**: `/gas-stations`  
**Controller**: `GasStationController`  
**View**: `resources/views/gas-stations/`

### Funcionalidades

Cadastro e gestão de postos de combustível.

### Campos do Cadastro

- **Nome**: Nome do posto
- **Endereço**: Endereço completo
- **Cidade**: Cidade
- **Estado**: Estado (UF)
- **Observações**: Notas adicionais

### Operações

- **Listar**: Visualizar todos os postos
- **Criar**: Cadastrar novo posto
- **Editar**: Atualizar informações
- **Excluir**: Remover posto

### Uso

Postos são referenciados nos registros de abastecimento.

---

## 📊 Relatórios

**Rota Base**: `/reports`  
**Controller**: `ReportController`  
**View**: `resources/views/reports/`

### Funcionalidades

Geração de relatórios e análises.

### Relatórios Disponíveis

Todos os relatórios possuem opção de exportação para Excel (`.xlsx`) e PDF.

#### 1. KM por Veículo

**Rota**: `/reports/km-by-vehicle`

- KM rodado por veículo em um período
- Filtros: período, veículo
- Exibição em tabela
- Exportação Excel/PDF

#### 2. Consumo Médio

**Rota**: `/reports/consumo`

- Cálculo de consumo médio por veículo
- Filtros: período, veículo
- Exportação Excel/PDF

#### 3. Custo de Combustível

**Rota**: `/reports/fuel-cost`

- Análise de custos de combustível
- Filtros: período, veículo
- Exportação Excel/PDF

#### 4. Custo de Combustível por Veículo

**Rota**: `/reports/fuel-cost-by-vehicle`

- Custo total de combustível por veículo
- Filtros: período, veículo
- Exibição em tabela
- Exportação Excel/PDF

#### 5. Abastecimentos

**Rota**: `/reports/fuelings`

- Histórico completo de abastecimentos
- Filtros: período, veículo, tipo de combustível
- Exportação Excel/PDF

#### 6. Manutenções

**Rota**: `/reports/maintenances`

- Histórico de manutenções
- Filtros: período, veículo, tipo
- Exibição em tabela
- Exportação Excel/PDF

#### 7. Manutenções Detalhadas

**Rota**: `/reports/maintenances-detailed`

- Relatório detalhado de manutenções
- Informações completas por veículo
- Exportação Excel/PDF

#### 8. Manutenções Programadas

**Rota**: `/reports/upcoming-maintenance`

- Manutenções próximas (por data e KM)
- Alertas de manutenções pendentes
- Exportação Excel/PDF

#### 9. Uso por Condutor

**Rota**: `/reports/driver-usage`

- Análise de uso por condutor
- KM e atividades por motorista
- Exportação Excel/PDF

#### 10. Auditoria de Odômetro

**Rota**: `/reports/odometer-audit`

- Histórico de alterações no odômetro
- Verificação de consistência
- Exportação Excel/PDF

#### 11. Rotas e Paradas

**Rota**: `/reports/routes-stops`

- Análise de rotas mais utilizadas
- Paradas intermediárias
- Exportação Excel/PDF

#### 12. Ranking

**Rota**: `/reports/ranking`

- Ranking de veículos por diversos critérios
- Comparações entre veículos
- Exportação Excel/PDF

#### 13. Relatório Consolidado

**Rota**: `/reports/consolidated`

- Visão consolidada de todos os dados
- Métricas gerais da frota
- Exportação Excel/PDF

#### 14. Revisões

**Rota**: `/reports/reviews`

- Relatório de revisões e notificações
- Status das revisões programadas
- Exportação Excel/PDF

### Permissões

- **Admin**: Acesso a todos os relatórios
- **Condutor**: Relatórios apenas dos veículos vinculados

### Exportação

Todos os relatórios suportam:
- **Excel** (`.xlsx`): Exportação completa para planilhas
- **PDF**: Geração de documento PDF formatado

---

## 📥 Importação

**Rota Base**: `/importacao`  
**Controller**: `ImportController`  
**View**: `resources/views/import/`

### Funcionalidades

Importação em massa de percursos via arquivo Excel.

### Processo de Importação

1. **Upload**: Usuário faz upload do arquivo Excel
2. **Validação**: Sistema valida formato e dados
3. **Processamento**: Job processa em background
4. **Acompanhamento**: Usuário acompanha progresso em tempo real
5. **Conclusão**: Notificação de conclusão

### Tipos de Importação

#### 1. Importação de Percursos

- **Formato**: `.xlsx` ou `.xls`
- **Estrutura**: Múltiplas abas suportadas
- **Colunas**: Seguir formato padrão
- **Campos Obrigatórios**: Ano, Veículo

#### 2. Importação de Locais

- **Rota**: `/importacao/locais`
- Importação em massa de locais via Excel
- Validação de dados

### Funcionalidades Adicionais

- **Exportar Modelo**: Baixar arquivo Excel com estrutura esperada
- **Acompanhamento de Progresso**: Barra de progresso em tempo real
- **Logs de Processamento**: Histórico detalhado
- **Status**: processando, concluído, erro

### Processamento em Background

- Utiliza Laravel Queue
- Não bloqueia a interface
- Permite múltiplas importações simultâneas
- Logs detalhados de cada importação

---

## 👥 Usuários

**Rota Base**: `/users`  
**Controller**: `UserController`  
**View**: `resources/views/users/`

### Funcionalidades

Gestão de usuários do sistema (apenas admin).

### Campos do Cadastro

- **Nome**: Nome completo
- **Nome Completo**: Nome completo para exibição
- **Email**: Email (usado para login)
- **Senha**: Senha de acesso
- **Role**: Função (admin, condutor)
- **Ativo**: Status do usuário
- **Avatar**: Foto de perfil (opcional)

### Operações

- **Listar**: Visualizar todos os usuários
- **Criar**: Cadastrar novo usuário
- **Editar**: Atualizar informações
- **Excluir**: Remover usuário
- **Permissões**: Gerenciar permissões por módulo

### Roles

- **Admin**: Acesso total ao sistema
- **Condutor**: Acesso limitado aos veículos vinculados

### Permissões por Módulo

- **can_view**: Visualizar
- **can_create**: Criar
- **can_edit**: Editar
- **can_delete**: Excluir

### Vinculação com Veículos

Condutores podem ser vinculados a veículos específicos.

---

## ⚙️ Configurações

**Rota Base**: `/settings`  
**Controller**: `SettingsController`  
**View**: `resources/views/settings/`

### Funcionalidades

Configurações gerais do sistema (apenas admin).

### Seções

#### 1. Configurações Gerais

- Configurações do sistema
- Valores armazenados em `system_settings`

#### 2. Aparência

- Personalização visual
- Tema claro/escuro (se implementado)
- Cores e estilos

#### 3. Preferências de Dashboard

- Filtros padrão
- Métricas exibidas
- Layout

#### 4. Tipos de Combustível

- Gerenciar tipos de combustível
- CRUD completo
- Vinculação com veículos

#### 5. Métodos de Pagamento

- Gerenciar métodos de pagamento
- CRUD completo
- Usado em abastecimentos

#### 6. Tipos de Manutenção

- Gerenciar tipos de manutenção
- CRUD completo
- Classificação de manutenções

#### 7. Tipos de Local

- Gerenciar tipos de local
- CRUD completo
- Categorização de locais

#### 8. Postos de Combustível

- Gerenciar postos de gasolina
- CRUD completo
- Referenciado em abastecimentos

#### 9. Módulos Padrão para Condutores

- Configurar módulos visíveis por padrão para condutores
- Personalização de acesso inicial

#### 10. Configurações de Email

- Configuração de servidor SMTP
- Teste de envio de email
- Personalização de templates

#### 11. Revisões e Obrigações Legais

- Configurações de notificações de revisão
- Alertas de obrigações legais
- Personalização de avisos

---

## 🔔 Notificações

**Rota Base**: `/notifications`  
**Controller**: `NotificationController`  
**View**: `resources/views/notifications/`

### Funcionalidades

Sistema de notificações do usuário.

### Tipos de Notificação

- **info**: Informações gerais (azul)
- **success**: Sucesso (verde)
- **warning**: Avisos (amarelo/laranja)
- **error**: Erros (vermelho)

### Operações

- **Listar**: Visualizar todas as notificações
- **Visualizar**: Ver detalhes e marcar como lida
- **Marcar como Lida**: Marcar notificação individual
- **Marcar Todas como Lidas**: Marcar todas de uma vez
- **Excluir**: Remover notificação

### Exibição

- **Ícone de Sino**: Menu superior com contador
- **Dropdown**: Últimas 5 notificações não lidas
- **Página Completa**: Lista completa de notificações

### Notificações Automáticas

- Notificações de revisão (quando KM é atingido)
- Notificações de importação (conclusão, erros)

### Links

Notificações podem ter links para páginas relacionadas.

---

## 🔐 Permissões e Acesso

### Sistema de Permissões

O sistema utiliza um sistema granular de permissões:

1. **Roles**: Função do usuário (admin, condutor)
2. **Módulos**: Módulos do sistema
3. **Ações**: view, create, edit, delete

### Políticas (Policies)

Cada módulo tem uma Policy que controla o acesso:
- `VehiclePolicy`
- `TripPolicy`
- `FuelingPolicy`
- `MaintenancePolicy`
- `UserPolicy`
- `ReviewNotificationPolicy`
- `SettingsPolicy`
- `VehicleMandatoryEventPolicy`
- `LocationPolicy`

### Middleware

- `auth`: Requer autenticação
- `verified`: Requer email verificado
- Policies: Verificam permissões específicas

---

## 📱 Responsividade

Todos os módulos são responsivos e funcionam em:
- Desktop
- Tablet
- Mobile

---

## 🔍 Busca e Filtros

A maioria dos módulos possui:
- Busca por texto
- Filtros por período
- Filtros por veículo
- Ordenação

---

Esta documentação cobre os principais módulos do sistema. Para detalhes técnicos específicos, consulte a [documentação de arquitetura](ARQUITETURA.md) e a [documentação de rotas](ROTAS_API.md).

