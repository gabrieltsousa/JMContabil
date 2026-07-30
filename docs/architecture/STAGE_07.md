# Etapa 7 — Checklist de entrega

## Entregue

- [x] Comando Artisan `charges:dispatch-daily`
- [x] Opções `--office=` e `--sync` (ops/debug)
- [x] Schedule em `routes/console.php` apontando para o comando
- [x] Timezone `America/Sao_Paulo` (config `jmcontabil.timezone`)
- [x] `withoutOverlapping` + `onOneServer` (quando cache distribuído)
- [x] Log em `storage/logs/charges-schedule.log`
- [x] App timezone sincronizado no `AppServiceProvider`
- [x] Feature tests do comando e do schedule

## Decisões

1. **Sem `Kernel.php`** — Laravel 11+ agenda em `routes/console.php` (equivalente).
2. **Schedule só chama Artisan** — zero regra de negócio no schedule.
3. **Fluxo:** `Schedule → charges:dispatch-daily → DispatchDailyChargesAction → Queue → DailyChargeService`
4. **`--sync`** — atalho operacional para rodar sem worker (não usado pelo cron).
5. **`onOneServer`** — só com Redis/database/dynamodb, evitando falha em cache file local.

## Produção

```cron
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

Worker (próxima etapa):

```bash
php artisan queue:work redis --queue=charges,whatsapp,default
```

## Próxima etapa

**Etapa 8 — Queue** (`ProcessDailyChargesJob`, `SendChargeWhatsAppJob`, retries=3, failed).
