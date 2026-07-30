# JM Contábil

SaaS MVP para escritórios de contabilidade: **automação de cobrança mensal via WhatsApp**.

> O objetivo do MVP **não** é fazer contabilidade — é enviar cobranças no dia do vencimento com chave PIX e registrar o histórico.

## Stack

| Camada | Tecnologia |
|--------|------------|
| Backend | Laravel 13, PHP 8.3+, MySQL, Redis, Queues, Scheduler |
| Auth API | Laravel Sanctum |
| Frontend | Vue 3, Vite, TypeScript, Tailwind CSS 4, Pinia, Vue Router |
| Arquitetura | Clean Architecture (`Domain` / `Application` / `Infrastructure` / `Shared`) |

## Funcionalidades

- Cadastro de clientes (nome, WhatsApp, valor, dia de vencimento, PIX, status)
- Scheduler diário → fila → envio WhatsApp (Fake local; **Evolution** para envio real)
- Histórico de envios (status, mensagem, resposta/erro)
- Dashboard com métricas
- Configurações (empresa, template com placeholders, provider, timezone)
- Preparado para multiempresa (`office_id`) e providers reais (Evolution, Z-API, Meta, etc.)

## Fluxo de cobrança

```
Scheduler (00:00 America/Sao_Paulo)
  → charges:dispatch-daily
    → ProcessDailyChargesJob
      → cria Charge + PaymentMethod
        → SendChargeWhatsAppJob (até 3 tries)
          → WhatsAppProvider (fake | evolution | …)
          → charge_deliveries + status
```

## WhatsApp real (Evolution API)

```bash
# Sobe MySQL, Redis, Postgres da Evolution e a API
docker compose up -d

# Cria instância + QR
php artisan whatsapp:evolution-setup
# Abra storage/app/evolution-qr.html e escaneie no WhatsApp

# .env
JM_WHATSAPP_PROVIDER=evolution
WHATSAPP_EVOLUTION_URL=http://127.0.0.1:8080
WHATSAPP_EVOLUTION_API_KEY=jm-contabil-evolution-key
WHATSAPP_EVOLUTION_INSTANCE=jmcontabil
```

No painel: Settings → provider **evolution**.

## Deploy

Este app precisa de **PHP + MySQL + Redis + filas + scheduler + Evolution 24/7**.  
**Vercel não serve** para o backend completo (sem processo long-running / Redis / Evolution).

Use o stack Docker de produção:

```bash
cp .env.example .env.production   # preencha APP_KEY, DB_*, APP_URL, Evolution
docker compose -f docker-compose.prod.yml up -d --build
```

Publique o código no GitHub e hospede o compose em um VPS (ou Railway/Render/Fly com Docker).

## Setup rápido

### 1. Dependências

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 2. Infra (MySQL + Redis)

```bash
docker compose up -d
```

Credenciais padrão no `.env.example`:

- MySQL: `jmcontabil` / `jmcontabil` / `secret` @ `127.0.0.1:3306`
- Redis: `127.0.0.1:6379`

### 3. Banco

```bash
php artisan migrate --seed
```

Admin seedado:

- E-mail: `admin@jmcontabil.test`
- Senha: `password`

### 4. Rodar

```bash
# API + SPA
php artisan serve
npm run dev

# Filas
php artisan queue:work redis --queue=charges,whatsapp,default --tries=3

# Scheduler (produção: crontab * * * * * php artisan schedule:run)
php artisan schedule:work
```

Painel: http://127.0.0.1:8000/login

## API (Sanctum)

| Método | Endpoint |
|--------|----------|
| POST | `/api/auth/login` |
| GET/POST/PUT/DELETE | `/api/customers` |
| GET | `/api/notifications` |
| POST | `/api/notifications/send` |
| GET | `/api/dashboard` |
| GET/PUT | `/api/settings` |

## Comandos úteis

```bash
# Disparo diário (enfileira)
php artisan charges:dispatch-daily

# Disparo síncrono (ops/debug)
php artisan charges:dispatch-daily --sync --office=1

# Testes
php artisan test

# Build frontend
npm run build
```

## Placeholders de mensagem

`{nome}` `{valor}` `{pix}` `{data}` `{empresa}` `{competencia}`

## Estrutura

```
app/
  Domain/           # Enums, VOs, contratos
  Application/      # Actions, DTOs, Services, Jobs, Events
  Infrastructure/   # Eloquent, WhatsApp providers
  Shared/           # Utilitários
  Http/             # Controllers, Requests, Policies, Resources
docs/architecture/  # Decisões por etapa
```

## Documentação arquitetural

Veja `docs/architecture/` (`OVERVIEW`, `LAYERS`, `DATA_MODEL`, `STAGE_01` … `STAGE_13`).

## Próximos passos (pós-MVP)

- Boleto e PIX Copia e Cola
- Confirmação automática de pagamento (webhooks)
- Multiempresa completo + portal do cliente
- Lembretes e chatbot
