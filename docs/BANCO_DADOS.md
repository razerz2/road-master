# Documentação do Banco de Dados

## 📊 Visão Geral

O banco de dados do SCKV foi projetado para suportar todas as funcionalidades do sistema de gestão de frotas, com relacionamentos bem definidos e índices otimizados.

## 🗄️ Estrutura do Banco

### Tabelas Principais

1. **users** - Usuários do sistema
2. **vehicles** - Veículos da frota
3. **trips** - Percursos/viagens
4. **fuelings** - Abastecimentos
5. **maintenances** - Manutenções
6. **locations** - Locais (origens, destinos, postos, etc.)
7. **review_notifications** - Notificações de revisão
8. **notifications** - Notificações do sistema
9. **modules** - Módulos do sistema
10. **user_module_permissions** - Permissões por módulo
11. **system_settings** - Configurações do sistema

### Tabelas de Relacionamento

- **user_vehicle** - Relacionamento muitos-para-muitos entre usuários e veículos
- **vehicle_fuel_type** - Relacionamento muitos-para-muitos entre veículos e tipos de combustível
- **trip_stops** - Paradas intermediárias de percursos

### Tabelas de Configuração

- **fuel_types** - Tipos de combustível
- **payment_methods** - Métodos de pagamento
- **maintenance_types** - Tipos de manutenção
- **location_types** - Tipos de local

### Tabelas de Sistema

- **import_logs** - Logs de importação
- **cache** - Cache do Laravel
- **jobs** - Jobs em fila
- **failed_jobs** - Jobs que falharam

## 📋 Esquema Detalhado

### users

Armazena os usuários do sistema.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| name | string | Nome do usuário |
| name_full | string | Nome completo |
| email | string | Email (único) |
| email_verified_at | timestamp | Data de verificação do email |
| password | string | Senha (hasheada) |
| role | string | Função (admin, condutor) |
| active | boolean | Status ativo/inativo |
| avatar | string | Caminho do avatar |
| preferences | json | Preferências do usuário |
| remember_token | string | Token de "lembrar-me" |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `email` (único)

**Relacionamentos**:
- `hasMany` Trip (como driver)
- `hasMany` Fueling
- `hasMany` Notification
- `belongsToMany` Vehicle

---

### vehicles

Armazena os veículos da frota.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| name | string | Nome do veículo |
| plate | string | Placa |
| brand | string | Marca |
| model | string | Modelo |
| year | integer | Ano |
| fuel_type | string | Tipo de combustível principal |
| tank_capacity | decimal(10,2) | Capacidade do tanque (litros) |
| km_inicial | integer | KM inicial |
| current_odometer | integer | Odômetro atual |
| active | boolean | Status ativo/inativo |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Relacionamentos**:
- `hasMany` Trip
- `hasMany` Fueling
- `hasMany` Maintenance
- `hasMany` ReviewNotification
- `belongsToMany` User
- `belongsToMany` FuelType

---

### trips

Armazena os percursos/viagens realizadas.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| vehicle_id | bigint | ID do veículo (FK) |
| driver_id | bigint | ID do condutor (FK users) |
| date | date | Data do percurso |
| origin_location_id | bigint | ID do local de origem (FK locations) |
| destination_location_id | bigint | ID do local de destino (FK locations) |
| return_to_origin | boolean | Retornou à origem |
| departure_time | time | Horário de saída |
| return_time | time | Horário de retorno |
| odometer_start | integer | Odômetro inicial |
| odometer_end | integer | Odômetro final |
| km_total | integer | KM total (calculado) |
| purpose | text | Finalidade do percurso |
| created_by | bigint | ID do usuário criador (FK users) |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `vehicle_id`
- `driver_id`
- `date`
- `origin_location_id`
- `destination_location_id`

**Relacionamentos**:
- `belongsTo` Vehicle
- `belongsTo` User (driver)
- `belongsTo` Location (origin)
- `belongsTo` Location (destination)
- `belongsTo` User (creator)
- `hasMany` TripStop

---

### trip_stops

Armazena as paradas intermediárias de percursos.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| trip_id | bigint | ID do percurso (FK) |
| location_id | bigint | ID do local (FK locations) |
| sequence | integer | Sequência da parada |
| notes | text | Observações |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `trip_id`
- `location_id`

**Relacionamentos**:
- `belongsTo` Trip
- `belongsTo` Location

---

### fuelings

Armazena os abastecimentos realizados.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| vehicle_id | bigint | ID do veículo (FK) |
| user_id | bigint | ID do usuário (FK users) |
| date_time | datetime | Data e hora do abastecimento |
| odometer | integer | Odômetro no momento |
| fuel_type | string | Tipo de combustível |
| liters | decimal(10,2) | Litros abastecidos |
| price_per_liter | decimal(10,2) | Preço por litro |
| total_amount | decimal(10,2) | Valor total (calculado) |
| gas_station_name | string | Nome do posto |
| payment_method | string | Método de pagamento (legado) |
| payment_method_id | bigint | ID do método de pagamento (FK) |
| notes | text | Observações |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `vehicle_id`
- `user_id`
- `date_time`
- `payment_method_id`

**Relacionamentos**:
- `belongsTo` Vehicle
- `belongsTo` User
- `belongsTo` PaymentMethod

---

### maintenances

Armazena as manutenções realizadas.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| vehicle_id | bigint | ID do veículo (FK) |
| date | date | Data da manutenção |
| odometer | integer | Odômetro no momento |
| type | string | Tipo de manutenção (legado) |
| maintenance_type_id | bigint | ID do tipo de manutenção (FK) |
| description | text | Descrição |
| provider | string | Fornecedor/oficina |
| cost | decimal(10,2) | Custo |
| next_due_date | date | Próxima data prevista |
| next_due_odometer | integer | Próximo KM previsto |
| notes | text | Observações |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `vehicle_id`
- `date`
- `maintenance_type_id`

**Relacionamentos**:
- `belongsTo` Vehicle
- `belongsTo` MaintenanceType

---

### locations

Armazena os locais (origens, destinos, postos, etc.).

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| name | string | Nome do local |
| type | string | Tipo de local (legado) |
| location_type_id | bigint | ID do tipo de local (FK) |
| address | string | Endereço |
| number | string | Número |
| complement | string | Complemento |
| neighborhood | string | Bairro |
| city | string | Cidade |
| state | string | Estado (UF) |
| zip_code | string | CEP |
| notes | text | Observações |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `location_type_id`

**Relacionamentos**:
- `belongsTo` LocationType
- `hasMany` Trip (como origin)
- `hasMany` Trip (como destination)
- `hasMany` TripStop

---

### review_notifications

Armazena as configurações de notificações de revisão.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| vehicle_id | bigint | ID do veículo (FK) |
| review_type | string | Tipo de revisão |
| name | string | Nome personalizado |
| current_km | integer | KM atual |
| notification_km | integer | KM para notificação |
| last_notified_km | integer | Último KM notificado |
| active | boolean | Status ativo/inativo |
| description | text | Descrição |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `vehicle_id`
- `active`

**Relacionamentos**:
- `belongsTo` Vehicle

---

### notifications

Armazena as notificações do sistema para usuários.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| user_id | bigint | ID do usuário (FK users) |
| type | string | Tipo (info, success, warning, error) |
| title | string | Título |
| message | text | Mensagem |
| link | string | Link relacionado |
| read | boolean | Lida/não lida |
| read_at | timestamp | Data de leitura |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `user_id`
- `read`
- `created_at`

**Relacionamentos**:
- `belongsTo` User

---

### modules

Armazena os módulos do sistema para controle de permissões.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| name | string | Nome do módulo |
| slug | string | Slug único |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `slug` (único)

**Relacionamentos**:
- `hasMany` UserModulePermission

---

### user_module_permissions

Armazena as permissões dos usuários por módulo.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| user_id | bigint | ID do usuário (FK users) |
| module_id | bigint | ID do módulo (FK modules) |
| can_view | boolean | Pode visualizar |
| can_create | boolean | Pode criar |
| can_edit | boolean | Pode editar |
| can_delete | boolean | Pode excluir |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `user_id`
- `module_id`
- `user_id` + `module_id` (único)

**Relacionamentos**:
- `belongsTo` User
- `belongsTo` Module

---

### user_vehicle

Tabela pivot para relacionamento muitos-para-muitos entre usuários e veículos.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| user_id | bigint | ID do usuário (FK users) |
| vehicle_id | bigint | ID do veículo (FK vehicles) |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `user_id`
- `vehicle_id`
- `user_id` + `vehicle_id` (único)

---

### vehicle_fuel_type

Tabela pivot para relacionamento muitos-para-muitos entre veículos e tipos de combustível.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| vehicle_id | bigint | ID do veículo (FK vehicles) |
| fuel_type_id | bigint | ID do tipo de combustível (FK fuel_types) |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `vehicle_id`
- `fuel_type_id`
- `vehicle_id` + `fuel_type_id` (único)

---

### fuel_types

Armazena os tipos de combustível.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| name | string | Nome do tipo |
| active | boolean | Status ativo/inativo |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

---

### payment_methods

Armazena os métodos de pagamento.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| name | string | Nome do método |
| active | boolean | Status ativo/inativo |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

---

### maintenance_types

Armazena os tipos de manutenção.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| name | string | Nome do tipo |
| active | boolean | Status ativo/inativo |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

---

### location_types

Armazena os tipos de local.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| name | string | Nome do tipo |
| active | boolean | Status ativo/inativo |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

---

### system_settings

Armazena as configurações do sistema.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| key | string | Chave da configuração (única) |
| value | string | Valor da configuração |
| type | string | Tipo (string, integer, boolean, etc.) |
| group | string | Grupo da configuração |
| description | text | Descrição |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `key` (único)
- `group`

---

### import_logs

Armazena os logs de importação.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| import_id | string | ID da importação |
| status | string | Status (processing, completed, error) |
| file_name | string | Nome do arquivo |
| total_rows | integer | Total de linhas |
| processed_rows | integer | Linhas processadas |
| errors | json | Erros encontrados |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

**Índices**:
- `import_id`

---

## 🔗 Relacionamentos Principais

### Diagrama de Relacionamentos

```
User
  ├── hasMany Trip (driver)
  ├── hasMany Fueling
  ├── hasMany Notification
  ├── belongsToMany Vehicle (via user_vehicle)
  └── hasMany UserModulePermission

Vehicle
  ├── hasMany Trip
  ├── hasMany Fueling
  ├── hasMany Maintenance
  ├── hasMany ReviewNotification
  ├── belongsToMany User (via user_vehicle)
  └── belongsToMany FuelType (via vehicle_fuel_type)

Trip
  ├── belongsTo Vehicle
  ├── belongsTo User (driver)
  ├── belongsTo Location (origin)
  ├── belongsTo Location (destination)
  ├── belongsTo User (creator)
  └── hasMany TripStop

Fueling
  ├── belongsTo Vehicle
  ├── belongsTo User
  └── belongsTo PaymentMethod

Maintenance
  ├── belongsTo Vehicle
  └── belongsTo MaintenanceType

Location
  ├── belongsTo LocationType
  ├── hasMany Trip (as origin)
  ├── hasMany Trip (as destination)
  └── hasMany TripStop

ReviewNotification
  └── belongsTo Vehicle

Notification
  └── belongsTo User

Module
  └── hasMany UserModulePermission

UserModulePermission
  ├── belongsTo User
  └── belongsTo Module
```

## 📊 Índices e Performance

### Índices Criados

- **Foreign Keys**: Todos os relacionamentos têm índices
- **Campos de Busca**: Nome, email, placa, etc.
- **Campos de Filtro**: Data, status, etc.
- **Campos Únicos**: Email, slug, etc.

### Otimizações

- **Eager Loading**: Uso de `with()` para evitar N+1 queries
- **Índices Compostos**: Para consultas frequentes
- **Soft Deletes**: Não implementado (exclusão física)

## 🔄 Migrations

Todas as tabelas são criadas através de migrations em `database/migrations/`.

### Ordem de Execução

As migrations devem ser executadas na ordem correta devido às dependências de foreign keys.

### Seeders

Dados iniciais são populados através de seeders:
- `DatabaseSeeder` - Seeder principal
- `FuelTypeSeeder` - Tipos de combustível
- `PaymentMethodSeeder` - Métodos de pagamento
- `MaintenanceTypeSeeder` - Tipos de manutenção
- `LocationTypeSeeder` - Tipos de local
- `DriverSeeder` - Usuários de exemplo
- `VehicleSeeder` - Veículos de exemplo
- `TripSeeder` - Percursos de exemplo
- `FuelingSeeder` - Abastecimentos de exemplo
- `MaintenanceSeeder` - Manutenções de exemplo

## 🔐 Integridade Referencial

### Foreign Keys

Todas as foreign keys são definidas nas migrations para garantir integridade referencial.

### Constraints

- **ON DELETE**: Restrict (padrão)
- **ON UPDATE**: Cascade (padrão)

## 📝 Convenções

- **Nomes de Tabelas**: Plural, snake_case
- **Nomes de Colunas**: snake_case
- **IDs**: `id` (bigint, auto_increment)
- **Timestamps**: `created_at`, `updated_at` (automáticos)
- **Foreign Keys**: `{table}_id` (ex: `vehicle_id`)

## 🔍 Consultas Comuns

### KM Total por Veículo

```sql
SELECT v.id, v.name, SUM(t.km_total) as total_km
FROM vehicles v
LEFT JOIN trips t ON t.vehicle_id = v.id
WHERE v.active = 1
GROUP BY v.id, v.name;
```

### Custo de Combustível por Veículo

```sql
SELECT v.id, v.name, SUM(f.total_amount) as total_cost
FROM vehicles v
LEFT JOIN fuelings f ON f.vehicle_id = v.id
WHERE v.active = 1
GROUP BY v.id, v.name;
```

### Notificações Não Lidas

```sql
SELECT COUNT(*) as unread_count
FROM notifications
WHERE user_id = ? AND read = 0;
```

---

Esta documentação cobre a estrutura completa do banco de dados. Para mais detalhes sobre as migrations, consulte os arquivos em `database/migrations/`.

