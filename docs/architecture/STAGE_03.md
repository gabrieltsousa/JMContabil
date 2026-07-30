# Etapa 3 — Checklist de entrega

## Entregue

- [x] `Office`, `Customer`, `Charge`, `ChargePaymentMethod`, `ChargeDelivery`, `Setting`
- [x] `User` atualizado com `office_id` + relação
- [x] Casts de Enums de domínio
- [x] Relacionamentos Eloquent
- [x] Scopes (`active`, `dueOn`, `sentToday`, `forOffice`, …)
- [x] Helpers de domínio no model (`money()`, `pixKey()`, `markSent()`, …)
- [x] Factories completas
- [x] Seeder usando Models
- [x] Testes de models (feature)

## Decisão: Models em Infrastructure

Models ficam em `app/Infrastructure/Persistence/Eloquent/Models`.

Motivo: Eloquent é detalhe de persistência. O Domain conhece contratos/VOs/Enums, não a ORM.
`User` permanece em `app/Models` por convenção do Laravel Auth.

## Próxima etapa

**Etapa 4 — Criar DTOs** de entrada/saída tipados para Actions e API.
