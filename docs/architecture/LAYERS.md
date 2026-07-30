# Mapa de camadas — JM Contábil

## Domain (`app/Domain`)

Regras puras de negócio. Sem HTTP, Eloquent ou Facades.

```
Domain/
├── Customer/
│   ├── Contracts/CustomerRepositoryInterface.php
│   ├── Enums/CustomerStatus.php
│   └── ValueObjects/{DueDay,PixKey}.php
├── Charge/
│   ├── Contracts/{Charge,ChargeDelivery}RepositoryInterface.php
│   ├── Enums/{ChargeStatus,PaymentMethodType,DeliveryStatus,DeliveryChannel}.php
│   └── ValueObjects/ReferenceMonth.php
├── Settings/
│   ├── Contracts/SettingRepositoryInterface.php
│   └── Enums/WhatsAppProvider.php
└── Shared/
    ├── Exceptions/InvalidValueObjectException.php
    └── ValueObjects/{PhoneNumber,Money}.php
```

## Application (`app/Application`)

Casos de uso e orquestração.

```
Application/
├── Actions/          → Um caso de uso por classe
├── DTOs/             → Transferência tipada entre camadas (Etapa 4)
├── Services/         → MessageTemplateService, DailyChargeService (Etapa 6)
├── Jobs/             → ProcessDailyChargesJob, SendChargeWhatsAppJob (Etapa 8)
├── Events/           → ChargeSent, ChargeFailed (Etapa 8+)
└── Listeners/        → Logging, métricas (Etapa 8+)
```

## Infrastructure (`app/Infrastructure`)

Detalhes técnicos e adapters.

```
Infrastructure/
├── Persistence/Eloquent/
│   ├── Models/       → Customer, Charge, ChargePaymentMethod, ChargeDelivery, Setting
│   └── Repositories/ → Implementações dos contratos Domain
└── WhatsApp/
    ├── Contracts/WhatsAppProviderInterface.php
    ├── DTOs/{PixChargeMessage,WhatsAppSendResult}.php
    └── Providers/FakeWhatsAppProvider.php
```

## Shared (`app/Shared`)

Cross-cutting sem regra de domínio.

```
Shared/
├── Support/MessagePlaceholders.php
└── Traits/           → apenas quando justificado
```

## Http (`app/Http`)

Borda da aplicação. Controllers finos.

```
Http/
├── Controllers/Api/
├── Requests/
├── Resources/
└── Policies/
```

## Fluxo de dependência

```
Http Controllers
      ↓
Application Actions / Services
      ↓
Domain Contracts + Value Objects
      ↑
Infrastructure (implementa contratos)
```

Controllers **nunca** chamam Repositories ou Providers diretamente.
