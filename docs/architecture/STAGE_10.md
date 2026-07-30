# Etapa 10 — Checklist de entrega

## Entregue

- [x] Laravel Sanctum (`HasApiTokens`)
- [x] Routes `routes/api.php`
- [x] Auth: login / logout / me
- [x] Customers CRUD
- [x] Notifications index + send
- [x] Dashboard + Settings
- [x] Form Requests
- [x] API Resources
- [x] Policies (Customer, Charge) com escopo por office
- [x] Controllers finos (delegam Actions/Services)
- [x] Feature tests da API

## Endpoints

| Método | Rota | Auth |
|--------|------|------|
| POST | `/api/auth/login` | público |
| POST | `/api/auth/logout` | Sanctum |
| GET | `/api/auth/me` | Sanctum |
| GET | `/api/dashboard` | Sanctum |
| GET/POST | `/api/customers` | Sanctum |
| GET/PUT/DELETE | `/api/customers/{id}` | Sanctum |
| GET | `/api/notifications` | Sanctum |
| POST | `/api/notifications/send` | Sanctum |
| GET/PUT | `/api/settings` | Sanctum |

## Decisões

1. Controllers só validam (FormRequest), autorizam (Policy) e chamam Action/Service.
2. Envio padrão é **async** (202 + Job); `async=false` envia síncrono.
3. Valor monetário na API de entrada em decimal (`350.00`); saída em centavos + formatted.
4. `BusinessRuleException` → HTTP 422 JSON.

## Próxima etapa

**Etapa 11 — Frontend Vue 3** (Dashboard, Clientes, Histórico, Configurações).
