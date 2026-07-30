# Etapa 5 — Checklist de entrega

## Entregue

- [x] `EloquentCustomerRepository`
- [x] `EloquentChargeRepository` (+ `createWithPaymentMethod` atômico)
- [x] `EloquentChargeDeliveryRepository`
- [x] `EloquentSettingRepository` (cache 10 min)
- [x] Bindings no `DomainServiceProvider`
- [x] Contratos Domain atualizados (`filter`, `countPaidInMonth`, etc.)
- [x] Feature tests dos repositórios

## Decisões

1. **Repositories retornam Models Eloquent** tipados na implementação; contratos usam `object` para não vazar Eloquent no Domain.
2. **`createWithPaymentMethod` em transação** — cobrança sem forma de pagamento nunca fica inconsistente.
3. **Eager loading** em `find`/`filter` de charges — evita N+1 na API/histórico.
4. **Settings com Cache** — leitura frequente no envio diário; invalidate no `updateOrCreate`.
5. **Query `like` só no Repository** — search nunca chega como SQL cru do frontend.

## Próxima etapa

**Etapa 6 — Services** (`DailyChargeService`, CRUD customers, dashboard, envio WhatsApp).
