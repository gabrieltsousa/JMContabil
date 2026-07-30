# Etapa 2 — Checklist de entrega

## Entregue

- [x] Migration `offices`
- [x] Migration `customers` (centavos, due_day 1–28, índices para scheduler)
- [x] Migration `charges` (competência + status + unique customer/mês)
- [x] Migration `charge_payment_methods` (payload JSON extensível)
- [x] Migration `charge_deliveries` (histórico/canal/retries/telemetria)
- [x] Migration `settings` (1 por office)
- [x] Migration `users.office_id` (multiusuário por escritório)
- [x] `docker-compose.yml` com MySQL 8.4 + Redis 7
- [x] `DatabaseSeeder` (office + settings + admin)
- [x] `migrate:fresh --seed` executado com sucesso

## Credenciais locais (Docker)

| Serviço | Host | Credenciais |
|---------|------|-------------|
| MySQL | `127.0.0.1:3306` | db `jmcontabil` / user `jmcontabil` / pass `secret` |
| Redis | `127.0.0.1:6379` | — |
| Admin | — | `admin@jmcontabil.test` / `password` |

```bash
docker compose up -d
php artisan migrate:fresh --seed
```

## Próxima etapa (após confirmação)

**Etapa 3 — Criar Models** Eloquent em Infrastructure + casts de Enums/relações.
