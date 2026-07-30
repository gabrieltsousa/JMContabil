# Schema — Etapa 2 (implementado)

Migrations criadas e aplicadas via `php artisan migrate:fresh --seed`.

## offices (preparação multiempresa)

| Coluna     | Tipo         | Notas                |
|------------|--------------|----------------------|
| id         | bigint PK    |                      |
| name       | string       |                      |
| slug       | string unique|                      |
| is_active  | boolean      | default true         |
| timestamps |              |                      |

## customers

| Coluna         | Tipo           | Notas                          |
|----------------|----------------|--------------------------------|
| id             | bigint PK      |                                |
| office_id      | FK nullable    | multiempresa                   |
| name           | string         |                                |
| phone          | string(20)     | E.164 sanitizado               |
| email          | string nullable|                                |
| pix_key        | string(77)     | VO PixKey                      |
| monthly_value  | unsignedBigInt | centavos                       |
| due_day        | unsignedTinyInt| 1–28                           |
| status         | string         | active/inactive                |
| timestamps     |                |                                |

Índices: `(office_id, status, due_day)`, `phone`, `status`, `due_day`

## charges

| Coluna           | Tipo           | Notas                              |
|------------------|----------------|------------------------------------|
| id               | bigint PK      |                                    |
| office_id        | FK nullable    |                                    |
| customer_id      | FK             | cascade                            |
| reference_month  | char(7)        | YYYY-MM                            |
| amount           | unsignedBigInt | centavos (snapshot)                |
| status           | string         | pending/sent/paid/overdue/failed   |
| due_date         | date           |                                    |
| sent_at          | timestamp null |                                    |
| paid_at          | timestamp null |                                    |
| message_sent     | text null      | corpo renderizado                  |
| failure_reason   | text null      |                                    |
| timestamps       |                |                                    |

Unique: `(customer_id, reference_month)`

## charge_payment_methods

| Coluna      | Tipo           | Notas                                    |
|-------------|----------------|------------------------------------------|
| id          | bigint PK      |                                          |
| charge_id   | FK             | cascade                                  |
| type        | string         | pix_key/pix_copia_cola/qr_code/boleto    |
| amount      | unsignedBigInt |                                          |
| payload     | json           | chave, EMV, linha digitável, etc.        |
| timestamps  |                |                                          |

## charge_deliveries

| Coluna              | Tipo           | Notas                         |
|---------------------|----------------|-------------------------------|
| id                  | bigint PK      |                               |
| charge_id           | FK             | cascade                       |
| channel             | string         | whatsapp/email/sms            |
| status              | string         | queued/sending/sent/failed…   |
| message             | text           |                               |
| provider            | string         | fake/evolution/…              |
| provider_message_id | string null    |                               |
| provider_response   | text null      |                               |
| error_message       | text null      |                               |
| duration_ms         | unsignedInt    |                               |
| attempt             | unsignedTinyInt|                               |
| sent_at             | timestamp null |                               |
| whatsapp_response   | text null      | resposta do cliente (futuro)  |
| timestamps          |                |                               |

## settings

| Coluna             | Tipo        | Notas                |
|--------------------|-------------|----------------------|
| id                 | bigint PK   |                      |
| office_id          | FK nullable | unique por office    |
| company_name       | string      |                      |
| default_message    | text        | template             |
| whatsapp_provider  | string      | enum                 |
| timezone           | string      | America/Sao_Paulo    |
| timestamps         |             |                      |

## users (extensão)

| Coluna     | Tipo        | Notas              |
|------------|-------------|--------------------|
| office_id  | FK nullable | multiusuário       |

## Relação com o pedido original

`payment_notifications` foi **substituído** por `charges` + `charge_deliveries` + `charge_payment_methods`.
