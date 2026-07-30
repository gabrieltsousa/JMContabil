# Etapa 8 — Checklist de entrega

## Entregue

- [x] `ProcessDailyChargesJob` (fila `charges`)
- [x] `SendChargeWhatsAppJob` (fila `whatsapp`, tries=3, backoff)
- [x] `DispatchDailyChargesAction` → `ProcessDailyChargesJob`
- [x] `DailyChargeService` com `viaQueue` (job por cliente)
- [x] `ChargeNotificationService` lança exceção para retry; marca failed só na última tentativa
- [x] Events `ChargeWhatsAppSent` / `ChargeWhatsAppFailed`
- [x] Listener de log
- [x] Feature tests dos Jobs

## Fluxo final

```
Scheduler
  → charges:dispatch-daily
    → DispatchDailyChargesAction
      → ProcessDailyChargesJob (queue: charges)
        → DailyChargeService (cria charges)
          → SendChargeWhatsAppJob × N (queue: whatsapp)
            → ChargeNotificationService
              → WhatsAppProvider
              → Persistência (delivery + status)
```

## Retries

| Config | Valor |
|--------|-------|
| `JM_WHATSAPP_MAX_TRIES` | 3 |
| Backoff | 10s, 30s, 60s |
| Após esgotar | `charge.status = failed` + event |

## Worker

```bash
php artisan queue:work redis --queue=charges,whatsapp,default --tries=3
```

## Próxima etapa

**Etapa 9 — Provider Fake** (já existe; reforçar/documentar contrato e testes).
