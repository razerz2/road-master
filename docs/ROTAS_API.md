# Documentação de Rotas e API

## 📋 Visão Geral

Este documento descreve todas as rotas disponíveis no sistema Road Master, organizadas por funcionalidade.

## 🔐 Autenticação

Todas as rotas (exceto as públicas) requerem autenticação via middleware `auth` e `verified`.

## 🌐 Rotas Públicas

### Arquivos do Storage

```
GET /files/{path}
```

Serve arquivos do storage quando o link simbólico não está disponível.

**Parâmetros**:
- `path` (string): Caminho do arquivo

**Controller**: `StorageController@serve`

---

## 🏠 Dashboard

### Visualizar Dashboard

```
GET /dashboard
```

Exibe o dashboard principal com métricas e estatísticas.

**Parâmetros de Query**:
- `start_date` (date, opcional): Data inicial do período
- `end_date` (date, opcional): Data final do período
- `vehicle_id` (integer, opcional): ID do veículo para filtrar

**Controller**: `DashboardController@index`

**View**: `dashboard`

---

## 🚗 Veículos

### Listar Veículos

```
GET /vehicles
```

Lista todos os veículos.

**Controller**: `VehicleController@index`

### Criar Veículo

```
GET /vehicles/create
POST /vehicles
```

Cria um novo veículo.

**Controller**: `VehicleController@create`, `VehicleController@store`

### Visualizar Veículo

```
GET /vehicles/{vehicle}
```

Exibe detalhes de um veículo.

**Controller**: `VehicleController@show`

### Editar Veículo

```
GET /vehicles/{vehicle}/edit
PUT /vehicles/{vehicle}
PATCH /vehicles/{vehicle}
```

Edita um veículo existente.

**Controller**: `VehicleController@edit`, `VehicleController@update`

### Excluir Veículo

```
DELETE /vehicles/{vehicle}
```

Exclui um veículo.

**Controller**: `VehicleController@destroy`

---

## 📍 Locais

### Listar Locais

```
GET /locations
```

Lista todos os locais.

**Controller**: `LocationController@index`

### Criar Local

```
GET /locations/create
POST /locations
```

Cria um novo local.

**Controller**: `LocationController@create`, `LocationController@store`

### Criar Local via AJAX

```
POST /locations/store-ajax
```

Cria um local via requisição AJAX (retorna JSON).

**Controller**: `LocationController@storeAjax`

**Resposta JSON**:
```json
{
  "id": 1,
  "name": "Nome do Local"
}
```

### Visualizar Local

```
GET /locations/{location}
```

Exibe detalhes de um local.

**Controller**: `LocationController@show`

### Editar Local

```
GET /locations/{location}/edit
PUT /locations/{location}
PATCH /locations/{location}
```

Edita um local existente.

**Controller**: `LocationController@edit`, `LocationController@update`

### Excluir Local

```
DELETE /locations/{location}
```

Exclui um local.

**Controller**: `LocationController@destroy`

---

## 🛣️ Percursos

### Listar Percursos

```
GET /trips
```

Lista todos os percursos.

**Controller**: `TripController@index`

### Criar Percurso

```
GET /trips/create
POST /trips
```

Cria um novo percurso.

**Controller**: `TripController@create`, `TripController@store`

### Obter Odômetro do Veículo

```
GET /trips/vehicle/{vehicleId}/odometer
```

Retorna o odômetro atual de um veículo (AJAX).

**Controller**: `TripController@getVehicleOdometer`

**Resposta JSON**:
```json
{
  "odometer": 15000
}
```

### Visualizar Percurso

```
GET /trips/{trip}
```

Exibe detalhes de um percurso.

**Controller**: `TripController@show`

### Editar Percurso

```
GET /trips/{trip}/edit
PUT /trips/{trip}
PATCH /trips/{trip}
```

Edita um percurso existente.

**Controller**: `TripController@edit`, `TripController@update`

### Excluir Percurso

```
DELETE /trips/{trip}
```

Exclui um percurso.

**Controller**: `TripController@destroy`

---

## ⛽ Abastecimentos

### Listar Abastecimentos

```
GET /fuelings
```

Lista todos os abastecimentos.

**Controller**: `FuelingController@index`

### Criar Abastecimento

```
GET /fuelings/create
POST /fuelings
```

Cria um novo abastecimento.

**Controller**: `FuelingController@create`, `FuelingController@store`

### Visualizar Abastecimento

```
GET /fuelings/{fueling}
```

Exibe detalhes de um abastecimento.

**Controller**: `FuelingController@show`

### Editar Abastecimento

```
GET /fuelings/{fueling}/edit
PUT /fuelings/{fueling}
PATCH /fuelings/{fueling}
```

Edita um abastecimento existente.

**Controller**: `FuelingController@edit`, `FuelingController@update`

### Excluir Abastecimento

```
DELETE /fuelings/{fueling}
```

Exclui um abastecimento.

**Controller**: `FuelingController@destroy`

---

## 🔧 Manutenções

### Listar Manutenções

```
GET /maintenances
```

Lista todas as manutenções.

**Controller**: `MaintenanceController@index`

### Criar Manutenção

```
GET /maintenances/create
POST /maintenances
```

Cria uma nova manutenção.

**Controller**: `MaintenanceController@create`, `MaintenanceController@store`

### Visualizar Manutenção

```
GET /maintenances/{maintenance}
```

Exibe detalhes de uma manutenção.

**Controller**: `MaintenanceController@show`

### Editar Manutenção

```
GET /maintenances/{maintenance}/edit
PUT /maintenances/{maintenance}
PATCH /maintenances/{maintenance}
```

Edita uma manutenção existente.

**Controller**: `MaintenanceController@edit`, `MaintenanceController@update`

### Excluir Manutenção

```
DELETE /maintenances/{maintenance}
```

Exclui uma manutenção.

**Controller**: `MaintenanceController@destroy`

---

## 🔔 Notificações de Revisão

### Listar Notificações de Revisão

```
GET /review-notifications
```

Lista todas as notificações de revisão.

**Controller**: `ReviewNotificationController@index`

### Criar Notificação de Revisão

```
GET /review-notifications/create
POST /review-notifications
```

Cria uma nova notificação de revisão.

**Controller**: `ReviewNotificationController@create`, `ReviewNotificationController@store`

### Visualizar Notificação de Revisão

```
GET /review-notifications/{reviewNotification}
```

Exibe detalhes de uma notificação de revisão.

**Controller**: `ReviewNotificationController@show`

### Editar Notificação de Revisão

```
GET /review-notifications/{reviewNotification}/edit
PUT /review-notifications/{reviewNotification}
PATCH /review-notifications/{reviewNotification}
```

Edita uma notificação de revisão existente.

**Controller**: `ReviewNotificationController@edit`, `ReviewNotificationController@update`

### Excluir Notificação de Revisão

```
DELETE /review-notifications/{reviewNotification}
```

Exclui uma notificação de revisão.

**Controller**: `ReviewNotificationController@destroy`

### Ativar/Desativar Notificação

```
POST /review-notifications/{reviewNotification}/toggle-active
```

Alterna o status ativo/inativo de uma notificação.

**Controller**: `ReviewNotificationController@toggleActive`

---

## 📊 Relatórios

### KM por Veículo

```
GET /reports/km-by-vehicle
```

Gera relatório de KM rodado por veículo.

**Parâmetros de Query**:
- `start_date` (date, opcional): Data inicial
- `end_date` (date, opcional): Data final
- `vehicle_id` (integer, opcional): Filtrar por veículo

**Controller**: `ReportController@kmByVehicle`

### Custo de Combustível por Veículo

```
GET /reports/fuel-cost-by-vehicle
```

Gera relatório de custo de combustível por veículo.

**Parâmetros de Query**:
- `start_date` (date, opcional): Data inicial
- `end_date` (date, opcional): Data final
- `vehicle_id` (integer, opcional): Filtrar por veículo

**Controller**: `ReportController@fuelCostByVehicle`

### Manutenções

```
GET /reports/maintenances
```

Gera relatório de manutenções.

**Parâmetros de Query**:
- `start_date` (date, opcional): Data inicial
- `end_date` (date, opcional): Data final
- `vehicle_id` (integer, opcional): Filtrar por veículo
- `maintenance_type_id` (integer, opcional): Filtrar por tipo

**Controller**: `ReportController@maintenances`

---

## 📥 Importação

### Página de Importação

```
GET /importacao
```

Exibe a página de importação.

**Controller**: `ImportController@index`

### Processar Importação

```
POST /importacao
```

Processa um arquivo Excel para importação.

**Parâmetros**:
- `file` (file, obrigatório): Arquivo Excel (.xlsx ou .xls)
- `year` (integer, obrigatório): Ano dos dados
- `vehicle_id` (integer, obrigatório): ID do veículo

**Controller**: `ImportController@import`

**Resposta**: Redireciona para página de progresso

### Acompanhar Progresso

```
GET /importacao/progresso/{id}
```

Exibe página de acompanhamento do progresso da importação.

**Parâmetros**:
- `id` (string): ID da importação

**Controller**: `ImportController@progress`

### Status da Importação (API)

```
GET /importacao/status/{id}
```

Retorna o status atual da importação (JSON).

**Parâmetros**:
- `id` (string): ID da importação

**Controller**: `ImportController@status`

**Resposta JSON**:
```json
{
  "status": "processing",
  "progress": 50,
  "total": 100,
  "processed": 50,
  "logs": [...]
}
```

---

## 🔔 Notificações

### Listar Notificações

```
GET /notifications
```

Lista todas as notificações do usuário.

**Controller**: `NotificationController@index`

### Visualizar Notificação

```
GET /notifications/{notification}
```

Exibe detalhes de uma notificação e marca como lida.

**Controller**: `NotificationController@show`

### Marcar como Lida

```
POST /notifications/{notification}/read
```

Marca uma notificação como lida.

**Controller**: `NotificationController@markAsRead`

### Marcar Todas como Lidas

```
POST /notifications/mark-all-read
```

Marca todas as notificações do usuário como lidas.

**Controller**: `NotificationController@markAllAsRead`

### Excluir Notificação

```
DELETE /notifications/{notification}
```

Exclui uma notificação.

**Controller**: `NotificationController@destroy`

### Contador de Não Lidas (API)

```
GET /notifications/api/unread-count
```

Retorna o número de notificações não lidas (JSON).

**Controller**: `NotificationController@unreadCount`

**Resposta JSON**:
```json
{
  "count": 5
}
```

### Últimas Notificações (API)

```
GET /notifications/api/latest
```

Retorna as últimas notificações não lidas (JSON).

**Controller**: `NotificationController@latest`

**Resposta JSON**:
```json
{
  "notifications": [
    {
      "id": 1,
      "type": "warning",
      "title": "Título",
      "message": "Mensagem",
      "link": "/vehicles/1",
      "created_at": "2025-01-01 10:00:00"
    }
  ]
}
```

---

## 👥 Usuários

### Listar Usuários

```
GET /users
```

Lista todos os usuários (apenas admin).

**Controller**: `UserController@index`

### Criar Usuário

```
GET /users/create
POST /users
```

Cria um novo usuário (apenas admin).

**Controller**: `UserController@create`, `UserController@store`

### Visualizar Usuário

```
GET /users/{user}
```

Exibe detalhes de um usuário (apenas admin).

**Controller**: `UserController@show`

### Editar Usuário

```
GET /users/{user}/edit
PUT /users/{user}
PATCH /users/{user}
```

Edita um usuário existente (apenas admin).

**Controller**: `UserController@edit`, `UserController@update`

### Excluir Usuário

```
DELETE /users/{user}
```

Exclui um usuário (apenas admin).

**Controller**: `UserController@destroy`

---

## ⚙️ Configurações

### Visualizar Configurações

```
GET /settings
```

Exibe a página de configurações (apenas admin).

**Controller**: `SettingsController@index`

### Atualizar Configurações

```
PUT /settings
```

Atualiza as configurações gerais (apenas admin).

**Controller**: `SettingsController@updateSettings`

### Atualizar Aparência

```
PUT /settings/appearance
POST /settings/appearance
```

Atualiza as configurações de aparência (apenas admin).

**Controller**: `SettingsController@updateAppearance`

### Resetar Aparência

```
POST /settings/appearance/reset
```

Reseta as configurações de aparência para o padrão (apenas admin).

**Controller**: `SettingsController@resetAppearance`

### Atualizar Preferências de Dashboard

```
PUT /settings/dashboard-preferences
```

Atualiza as preferências do dashboard (apenas admin).

**Controller**: `SettingsController@updateDashboardPreferences`

---

## 🔧 Tipos de Combustível

### Listar Tipos de Combustível

```
GET /fuel-types
```

Lista todos os tipos de combustível (apenas admin).

**Controller**: `FuelTypeController@index`

### Criar Tipo de Combustível

```
POST /fuel-types
```

Cria um novo tipo de combustível (apenas admin).

**Controller**: `FuelTypeController@store`

### Atualizar Tipo de Combustível

```
PUT /fuel-types/{fuelType}
```

Atualiza um tipo de combustível (apenas admin).

**Controller**: `FuelTypeController@update`

### Excluir Tipo de Combustível

```
DELETE /fuel-types/{fuelType}
```

Exclui um tipo de combustível (apenas admin).

**Controller**: `FuelTypeController@destroy`

---

## 💳 Métodos de Pagamento

### Listar Métodos de Pagamento

```
GET /payment-methods
```

Lista todos os métodos de pagamento (apenas admin).

**Controller**: `PaymentMethodController@index`

### Criar Método de Pagamento

```
POST /payment-methods
```

Cria um novo método de pagamento (apenas admin).

**Controller**: `PaymentMethodController@store`

### Atualizar Método de Pagamento

```
PUT /payment-methods/{paymentMethod}
```

Atualiza um método de pagamento (apenas admin).

**Controller**: `PaymentMethodController@update`

### Excluir Método de Pagamento

```
DELETE /payment-methods/{paymentMethod}
```

Exclui um método de pagamento (apenas admin).

**Controller**: `PaymentMethodController@destroy`

---

## 🔧 Tipos de Manutenção

### Listar Tipos de Manutenção

```
GET /maintenance-types
```

Lista todos os tipos de manutenção (apenas admin).

**Controller**: `MaintenanceTypeController@index`

### Criar Tipo de Manutenção

```
POST /maintenance-types
```

Cria um novo tipo de manutenção (apenas admin).

**Controller**: `MaintenanceTypeController@store`

### Atualizar Tipo de Manutenção

```
PUT /maintenance-types/{maintenanceType}
```

Atualiza um tipo de manutenção (apenas admin).

**Controller**: `MaintenanceTypeController@update`

### Excluir Tipo de Manutenção

```
DELETE /maintenance-types/{maintenanceType}
```

Exclui um tipo de manutenção (apenas admin).

**Controller**: `MaintenanceTypeController@destroy`

---

## 📍 Tipos de Local

### Listar Tipos de Local

```
GET /location-types
```

Lista todos os tipos de local (apenas admin).

**Controller**: `LocationTypeController@index`

### Criar Tipo de Local

```
POST /location-types
```

Cria um novo tipo de local (apenas admin).

**Controller**: `LocationTypeController@store`

### Atualizar Tipo de Local

```
PUT /location-types/{locationType}
```

Atualiza um tipo de local (apenas admin).

**Controller**: `LocationTypeController@update`

### Excluir Tipo de Local

```
DELETE /location-types/{locationType}
```

Exclui um tipo de local (apenas admin).

**Controller**: `LocationTypeController@destroy`

---

## 👤 Perfil

### Editar Perfil

```
GET /profile
PUT /profile
PATCH /profile
```

Edita o perfil do usuário autenticado.

**Controller**: `ProfileController@edit`, `ProfileController@update`

### Excluir Conta

```
DELETE /profile
```

Exclui a conta do usuário autenticado.

**Controller**: `ProfileController@destroy`

---

## 🔐 Autenticação

As rotas de autenticação estão definidas em `routes/auth.php` e incluem:

- Login
- Registro
- Recuperação de senha
- Verificação de email
- Confirmação de senha

---

## 📝 Convenções de Rotas

### RESTful

A maioria das rotas segue o padrão RESTful:

- `GET /resource` - Listar
- `GET /resource/create` - Formulário de criação
- `POST /resource` - Criar
- `GET /resource/{id}` - Visualizar
- `GET /resource/{id}/edit` - Formulário de edição
- `PUT/PATCH /resource/{id}` - Atualizar
- `DELETE /resource/{id}` - Excluir

### Nomes de Rotas

Todas as rotas têm nomes definidos usando `->name()`:

- `dashboard`
- `vehicles.index`, `vehicles.create`, `vehicles.store`, etc.
- `trips.index`, `trips.create`, etc.

### Middleware

- `auth`: Requer autenticação
- `verified`: Requer email verificado
- Policies: Verificam permissões específicas

---

## 🔍 Busca e Filtros

Muitas rotas suportam parâmetros de query para busca e filtros:

- `?search=termo` - Busca por texto
- `?start_date=2025-01-01` - Data inicial
- `?end_date=2025-12-31` - Data final
- `?vehicle_id=1` - Filtrar por veículo
- `?page=1` - Paginação

---

## 📊 Respostas JSON

Algumas rotas retornam JSON (marcadas como API):

- `/notifications/api/unread-count`
- `/notifications/api/latest`
- `/importacao/status/{id}`
- `/trips/vehicle/{vehicleId}/odometer`
- `/locations/store-ajax`

---

## 🛡️ Segurança

- Todas as rotas (exceto públicas) requerem autenticação
- CSRF protection em formulários
- Validação de dados via Form Requests
- Autorização via Policies
- Sanitização de inputs

---

Para mais detalhes sobre cada rota, consulte os controllers em `app/Http/Controllers/`.

