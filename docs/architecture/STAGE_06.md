# Etapa 6 — Checklist de entrega

## Entregue

### Services
- [x] `CustomerService`
- [x] `ChargeService`
- [x] `ChargeNotificationService` (template + WhatsApp + histórico)
- [x] `DailyChargeService` (clientes do dia → charge → envio)
- [x] `DashboardService`
- [x] `SettingsService`
- [x] `MessageTemplateService` (já existia)

### Actions
- [x] CRUD Customer Actions
- [x] `SendChargeNotificationAction`
- [x] `DispatchDailyChargesAction` (enfileira `DailyChargeService`)
- [x] `GetDashboardStatsAction`
- [x] `UpdateSettingsAction`

### Testes
- [x] Feature tests dos services

## Decisões

1. **Services orquestram; Repositories persistem; Providers enviam.**
2. **`ChargeNotificationService` é o único ponto de envio WhatsApp** — logs, delivery e status da charge centralizados.
3. **`DailyChargeService` é idempotente por competência** — não duplica charge no mesmo mês.
4. **Scheduler → Action → queue closure → Service** — envio nunca no Scheduler. Job concreto na Etapa 8.
5. **Dias 29–31** — processamento diário é no-op (due_day máx. 28).

## Próxima etapa

**Etapa 7 — Scheduler** (refinar schedule, comando Artisan, timezone).
