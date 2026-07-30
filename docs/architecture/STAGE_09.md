# Etapa 9 — Checklist de entrega

## Entregue

- [x] `WhatsAppProviderInterface` com `driver()`
- [x] `FakeWhatsAppProvider` reforçado (normaliza telefone, inbox, fail forçado)
- [x] `FakeWhatsAppInbox` para asserts em testes
- [x] `WhatsAppProviderResolver` (enum → adapter)
- [x] Stubs: Evolution, Z-API, Meta Cloud, UltraMSG, 360Dialog
- [x] Config de credenciais placeholder por provider
- [x] `ChargeNotificationService` resolve provider a partir das Settings do office
- [x] Unit tests do Fake + Resolver + stubs

## Decisões

1. **Open/Closed** — novo provider = nova classe + case no Resolver; Domain intacto.
2. **Settings do office mandam** — `whatsapp_provider` no settings escolhe o adapter em runtime.
3. **Stubs explícitos** — providers futuros lançam `WhatsAppProviderNotConfiguredException` (fail-fast).
4. **Fake com `should_fail`** — simula retries sem API real (`JM_WHATSAPP_FAKE_FAIL=true`).

## Trocar para Evolution (futuro)

1. Implementar `EvolutionWhatsAppProvider` (remover extends Stub).
2. Preencher `JM_WHATSAPP_*` / `WHATSAPP_EVOLUTION_*`.
3. Em Settings: `whatsapp_provider = evolution`.

## Próxima etapa

**Etapa 10 — API REST** (customers, notifications, Sanctum).
