# JM Contábil — Arquitetura do MVP

## Objetivo

Automatizar a cobrança mensal de clientes de escritórios de contabilidade via WhatsApp, com arquitetura preparada para evolução (boletos, NF, multiempresa, IA, portal do cliente).

## Stack

| Camada     | Tecnologia                                      |
|------------|-------------------------------------------------|
| Framework  | Laravel 13 (PHP ^8.3)                           |
| Banco      | MySQL                                           |
| Cache/Fila | Redis                                           |
| Frontend   | Vue 3 + Vite + TypeScript + Tailwind + shadcn/vue |
| Auth       | Laravel Sanctum                                 |

> **Nota:** O ambiente instalou Laravel 13 (atual estável). PHP local é 8.3.6. A arquitetura permanece compatível com a especificação Laravel 12 / PHP 8.4.

## Camadas (Clean Architecture)

```
app/
├── Domain/           → Regras de negócio puras (sem Laravel)
├── Application/      → Casos de uso, Actions, DTOs, Jobs, Events
├── Infrastructure/   → Eloquent, WhatsApp providers, Cache, Logging
├── Shared/           → Utilitários transversais (sem helpers globais)
└── Http/             → Controllers finos, Form Requests, Resources, Policies
```

### Dependências permitidas

```
Http → Application → Domain
         ↓
Infrastructure → Domain (implementa contratos)
```

- `Domain` **não** depende de Laravel, Eloquent ou HTTP.
- `Application` orquestra Domain + contratos; não conhece detalhes de provider.
- `Infrastructure` implementa interfaces do Domain.
- `Http` apenas valida entrada, autoriza e delega para Actions.

## Modelo de domínio (MVP evoluído)

Em vez de apenas `payment_notifications`, o núcleo é **Charge (Cobrança)**:

| Entidade              | Responsabilidade                                      |
|-----------------------|-------------------------------------------------------|
| Customer              | Cliente do escritório (WhatsApp, valor, dia vencimento)|
| Charge                | Cobrança mensal por competência (mês/ano)             |
| ChargePaymentMethod   | Forma de pagamento da cobrança (PIX, boleto futuro)   |
| ChargeDelivery        | Histórico de envio WhatsApp / canal                   |
| Setting               | Configuração do escritório                            |
| Office (preparado)    | Multiempresa — coluna `office_id` desde o MVP         |

### Status da Charge

`pending` → `sent` → `paid` | `overdue` | `failed`

### Tipos de pagamento (extensível)

`pix_key` | `pix_copia_cola` | `qr_code` | `boleto`

## Fluxo de cobrança diária

```
Scheduler (00:00)
    ↓
DispatchDailyChargesAction   ← única responsabilidade do schedule
    ↓
ProcessDailyChargesJob       ← fila
    ↓
DailyChargeService           ← orquestra domínio
    ↓
CreateChargeAction + SendChargeWhatsAppJob (por cliente)
    ↓
WhatsAppProviderInterface    ← Fake no MVP
    ↓
Persistência (Charge + ChargeDelivery)
```

**Regra:** Scheduler nunca envia mensagem. Apenas dispara Action → Job.

## Princípios SOLID aplicados

| Princípio | Como |
|-----------|------|
| S | Actions com uma responsabilidade; Controllers sem regra |
| O | Novos providers WhatsApp sem alterar Domain |
| L | Qualquer `WhatsAppProviderInterface` é substituível |
| I | Repositories e Providers com interfaces focadas |
| D | Binding via Service Provider; Domain depende de abstrações |

## Multiempresa (preparação)

Todas as tabelas de negócio terão `office_id` (nullable no MVP single-tenant, obrigatório depois). Policies e scopes filtrarão por office desde o início da Etapa 5+.

## Escalabilidade prevista

- Jobs por cliente → paralelismo na fila Redis
- Índices em `due_day`, `status`, `office_id`, `reference_month`
- Cache de Settings por office
- Provider WhatsApp pluggable
- Webhooks futuros em `Infrastructure/WhatsApp/Webhooks`
