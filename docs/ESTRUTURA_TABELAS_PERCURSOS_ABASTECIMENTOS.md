# Estrutura das Tabelas - Percursos e Abastecimentos

Este documento descreve a estrutura completa das tabelas relacionadas a **Percursos (Trips)** e **Abastecimentos (Fuelings)** no sistema Road Master.

---

## 📋 Índice

1. [Tabela: `trips` (Percursos)](#tabela-trips-percursos)
2. [Tabela: `trip_stops` (Paradas dos Percursos)](#tabela-trip_stops-paradas-dos-percursos)
3. [Tabela: `fuelings` (Abastecimentos)](#tabela-fuelings-abastecimentos)
4. [Relacionamentos](#relacionamentos)

---

## Tabela: `trips` (Percursos)

A tabela `trips` armazena informações sobre os percursos realizados pelos veículos da frota.

### Estrutura da Tabela

| Campo | Tipo | Descrição | Restrições |
|-------|------|-----------|------------|
| `id` | `bigint unsigned` | Identificador único do percurso | Primary Key, Auto Increment |
| `vehicle_id` | `bigint unsigned` | ID do veículo que realizou o percurso | Foreign Key → `vehicles.id`, NOT NULL, ON DELETE CASCADE |
| `driver_id` | `bigint unsigned` | ID do motorista/condutor | Foreign Key → `users.id`, NOT NULL, ON DELETE CASCADE |
| `date` | `date` | Data do percurso | NOT NULL |
| `origin_location_id` | `bigint unsigned` | ID da localização de origem | Foreign Key → `locations.id`, NOT NULL, ON DELETE RESTRICT |
| `destination_location_id` | `bigint unsigned` | ID da localização de destino | Foreign Key → `locations.id`, NOT NULL, ON DELETE RESTRICT |
| `return_to_origin` | `boolean` | Indica se o veículo retornou à origem | Default: `false` |
| `departure_time` | `time` | Horário de saída | NOT NULL |
| `return_time` | `time` | Horário de retorno | NULLABLE |
| `odometer_start` | `integer` | Quilometragem inicial do odômetro | NOT NULL |
| `odometer_end` | `integer` | Quilometragem final do odômetro | NOT NULL |
| `km_total` | `integer` | Total de quilômetros rodados (calculado automaticamente) | NOT NULL |
| `purpose` | `text` | Finalidade/motivo do percurso | NULLABLE |
| `created_by` | `bigint unsigned` | ID do usuário que criou o registro | Foreign Key → `users.id`, NOT NULL, ON DELETE RESTRICT |
| `created_at` | `timestamp` | Data e hora de criação do registro | Auto |
| `updated_at` | `timestamp` | Data e hora da última atualização | Auto |

### Observações Importantes

- **Cálculo Automático de KM**: O campo `km_total` é calculado automaticamente pelo modelo quando `odometer_end` e `odometer_start` estão definidos: `km_total = odometer_end - odometer_start`
- **Campo `created_by`**: Sempre deve ser preenchido, mesmo em importações em background. O modelo possui lógica especial para garantir isso.
- **Cascata de Exclusão**: 
  - Se um veículo for excluído, todos os seus percursos são excluídos automaticamente
  - Se um usuário (motorista) for excluído, todos os seus percursos são excluídos
  - Se um usuário criador for excluído, a exclusão é restrita (não permite excluir se houver percursos criados por ele)

### Relacionamentos (Eloquent)

- `vehicle()` → `BelongsTo` → `Vehicle`
- `driver()` → `BelongsTo` → `User` (via `driver_id`)
- `originLocation()` → `BelongsTo` → `Location` (via `origin_location_id`)
- `destinationLocation()` → `BelongsTo` → `Location` (via `destination_location_id`)
- `creator()` → `BelongsTo` → `User` (via `created_by`)
- `stops()` → `HasMany` → `TripStop` (ordenado por `sequence`)

---

## Tabela: `trip_stops` (Paradas dos Percursos)

A tabela `trip_stops` armazena as paradas intermediárias de um percurso, permitindo registrar múltiplas paradas entre a origem e o destino.

### Estrutura da Tabela

| Campo | Tipo | Descrição | Restrições |
|-------|------|-----------|------------|
| `id` | `bigint unsigned` | Identificador único da parada | Primary Key, Auto Increment |
| `trip_id` | `bigint unsigned` | ID do percurso ao qual a parada pertence | Foreign Key → `trips.id`, NOT NULL, ON DELETE CASCADE |
| `location_id` | `bigint unsigned` | ID da localização da parada | Foreign Key → `locations.id`, NOT NULL, ON DELETE RESTRICT |
| `sequence` | `integer` | Ordem da parada no percurso (1, 2, 3, etc.) | NOT NULL |
| `created_at` | `timestamp` | Data e hora de criação do registro | Auto |
| `updated_at` | `timestamp` | Data e hora da última atualização | Auto |

### Índices e Constraints

- **Índice Único**: `unique(['trip_id', 'sequence'])` - Garante que não haja sequências duplicadas no mesmo percurso
- **Cascata de Exclusão**: Se um percurso for excluído, todas as suas paradas são excluídas automaticamente

### Relacionamentos (Eloquent)

- `trip()` → `BelongsTo` → `Trip`
- `location()` → `BelongsTo` → `Location`

---

## Tabela: `fuelings` (Abastecimentos)

A tabela `fuelings` armazena informações sobre os abastecimentos realizados pelos veículos da frota.

### Estrutura da Tabela

| Campo | Tipo | Descrição | Restrições |
|-------|------|-----------|------------|
| `id` | `bigint unsigned` | Identificador único do abastecimento | Primary Key, Auto Increment |
| `vehicle_id` | `bigint unsigned` | ID do veículo abastecido | Foreign Key → `vehicles.id`, NOT NULL, ON DELETE CASCADE |
| `user_id` | `bigint unsigned` | ID do usuário que registrou o abastecimento | Foreign Key → `users.id`, NOT NULL, ON DELETE CASCADE |
| `date_time` | `datetime` | Data e hora do abastecimento | NOT NULL |
| `odometer` | `integer` | Quilometragem do odômetro no momento do abastecimento | NOT NULL |
| `fuel_type` | `string(255)` | Tipo de combustível (ex: "Gasolina", "Diesel", "Etanol") | NOT NULL |
| `liters` | `decimal(8,2)` | Quantidade de litros abastecidos | NOT NULL, 2 casas decimais |
| `price_per_liter` | `decimal(8,2)` | Preço por litro do combustível | NOT NULL, 2 casas decimais |
| `total_amount` | `decimal(10,2)` | Valor total do abastecimento | NOT NULL, 2 casas decimais |
| `gas_station_name` | `string(255)` | Nome do posto de combustível | NULLABLE |
| `payment_method` | `string(255)` | Método de pagamento (legado, mantido para compatibilidade) | NULLABLE |
| `payment_method_id` | `bigint unsigned` | ID do método de pagamento | Foreign Key → `payment_methods.id`, NULLABLE |
| `notes` | `text` | Observações adicionais sobre o abastecimento | NULLABLE |
| `created_at` | `timestamp` | Data e hora de criação do registro | Auto |
| `updated_at` | `timestamp` | Data e hora da última atualização | Auto |

### Observações Importantes

- **Cálculo Automático de Valor Total**: Se o campo `total_amount` não for informado, o sistema calcula automaticamente: `total_amount = liters * price_per_liter`
- **Atualização de Odômetro**: Quando um abastecimento é criado ou atualizado, se o odômetro informado for maior que o `current_odometer` do veículo, o odômetro do veículo é atualizado automaticamente
- **Campo `fuel_type`**: Armazena o nome do tipo de combustível como string. Os tipos disponíveis são gerenciados pela tabela `fuel_types`, mas o valor salvo é o nome (string)
- **Campo `payment_method`**: Mantido para compatibilidade com dados antigos. O campo preferencial é `payment_method_id`
- **Cascata de Exclusão**: 
  - Se um veículo for excluído, todos os seus abastecimentos são excluídos automaticamente
  - Se um usuário for excluído, todos os seus abastecimentos são excluídos

### Relacionamentos (Eloquent)

- `vehicle()` → `BelongsTo` → `Vehicle`
- `user()` → `BelongsTo` → `User`
- `paymentMethod()` → `BelongsTo` → `PaymentMethod` (via `payment_method_id`)

---

## Relacionamentos

### Diagrama de Relacionamentos

```
┌─────────────┐
│   vehicles  │
└──────┬──────┘
       │
       ├─────────────────┐
       │                 │
       ▼                 ▼
┌─────────────┐    ┌─────────────┐
│    trips    │    │  fuelings   │
└──────┬──────┘    └─────────────┘
       │
       │
       ▼
┌─────────────┐
│ trip_stops  │
└─────────────┘

┌─────────────┐
│   users     │
└──────┬──────┘
       │
       ├─────────────────┐
       │                 │
       ▼                 ▼
┌─────────────┐    ┌─────────────┐
│    trips    │    │  fuelings   │
│ (driver_id) │    │  (user_id)  │
└─────────────┘    └─────────────┘

┌─────────────┐
│  locations  │
└──────┬──────┘
       │
       ├─────────────────┐
       │                 │
       ▼                 ▼
┌─────────────┐    ┌─────────────┐
│    trips    │    │ trip_stops  │
│(origin/dest)│    │(location_id)│
└─────────────┘    └─────────────┘

┌──────────────────┐
│ payment_methods  │
└────────┬─────────┘
         │
         ▼
┌─────────────┐
│  fuelings   │
│(payment_meth│
│od_id)       │
└─────────────┘
```

### Resumo dos Relacionamentos

#### Tabela `trips`
- **Pertence a**: `Vehicle`, `User` (driver), `User` (creator), `Location` (origin), `Location` (destination)
- **Tem muitos**: `TripStop`

#### Tabela `trip_stops`
- **Pertence a**: `Trip`, `Location`

#### Tabela `fuelings`
- **Pertence a**: `Vehicle`, `User`, `PaymentMethod` (opcional)

---

## Exemplos de Uso

### Criar um Percurso

```php
$trip = Trip::create([
    'vehicle_id' => 1,
    'driver_id' => 2,
    'date' => '2025-12-15',
    'origin_location_id' => 5,
    'destination_location_id' => 10,
    'return_to_origin' => true,
    'departure_time' => '08:00:00',
    'return_time' => '18:30:00',
    'odometer_start' => 50000,
    'odometer_end' => 50150,
    'purpose' => 'Entrega de mercadorias',
    'created_by' => auth()->id(),
]);
// km_total será calculado automaticamente: 150 km
```

### Criar um Abastecimento

```php
$fueling = Fueling::create([
    'vehicle_id' => 1,
    'user_id' => auth()->id(),
    'date_time' => '2025-12-15 14:30:00',
    'odometer' => 50150,
    'fuel_type' => 'Gasolina',
    'liters' => 45.50,
    'price_per_liter' => 5.89,
    'gas_station_name' => 'Posto Shell',
    'payment_method_id' => 1,
    'notes' => 'Abastecimento completo',
]);
// total_amount será calculado automaticamente: 268.00
```

### Adicionar Paradas a um Percurso

```php
$trip = Trip::find(1);

$trip->stops()->create([
    'location_id' => 7,
    'sequence' => 1,
]);

$trip->stops()->create([
    'location_id' => 8,
    'sequence' => 2,
]);
```

---

## Notas de Desenvolvimento

1. **Integridade Referencial**: As foreign keys garantem a integridade dos dados, mas algumas usam `ON DELETE RESTRICT` para evitar exclusões acidentais de dados importantes.

2. **Cálculos Automáticos**: Tanto `trips.km_total` quanto `fuelings.total_amount` são calculados automaticamente pelo modelo, mas podem ser sobrescritos se necessário.

3. **Campo `created_by` em Trips**: Este campo é crítico e sempre deve ser preenchido. O modelo possui lógica especial para garantir isso mesmo em importações em background.

4. **Compatibilidade**: O campo `payment_method` (string) em `fuelings` é mantido para compatibilidade com dados antigos, mas o uso preferencial é `payment_method_id`.

---

**Última atualização**: Dezembro 2025

