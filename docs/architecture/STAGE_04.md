# Etapa 4 — Checklist de entrega

## Entregue

### Contrato
- [x] `DataTransferObject` (`fromArray` / `toArray`)

### Customer
- [x] `CustomerData` (leitura + `fromModel`)
- [x] `CreateCustomerData` (normaliza phone/pix/money/due_day)
- [x] `UpdateCustomerData` (parcial, só campos presentes)
- [x] `CustomerFilterData`

### Charge
- [x] `ChargeData`
- [x] `CreateChargeData` (+ `fromCustomerSnapshot` para o job diário)
- [x] `ChargeFilterData`
- [x] `SendChargeNotificationData`
- [x] `ChargeDeliveryData`

### Settings / Dashboard
- [x] `SettingsData` / `UpdateSettingsData`
- [x] `DashboardStatsData`

### Testes
- [x] Unit tests de normalização e serialização

## Decisões

1. **DTOs readonly** — imutáveis após criação; thread-safe na fila.
2. **Value Objects no fromArray** — sanitização de telefone/PIX/valor acontece ao construir o DTO, não no Controller.
3. **`toPersistenceArray()`** — separa representação de API da persistência.
4. **Update parcial** — `presentKeys` evita sobrescrever campos omitidos com null.
5. **`fromCustomerSnapshot`** — facilita o Scheduler/Job criar cobrança a partir do cliente sem montar array manual.

## Próxima etapa

**Etapa 5 — Repositories** implementando os contratos Domain com Eloquent.
