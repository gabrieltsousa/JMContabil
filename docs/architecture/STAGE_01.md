# Etapa 1 — Checklist de entrega

## Entregue

- [x] Projeto Laravel (13.23 / PHP 8.3) inicializado
- [x] Estrutura Clean Architecture: Domain / Application / Infrastructure / Shared / Http
- [x] Enums de domínio (Customer, Charge, Delivery, Settings)
- [x] Value Objects (PhoneNumber, Money, DueDay, PixKey, ReferenceMonth)
- [x] Contratos de Repository (Customer, Charge, ChargeDelivery, Setting)
- [x] WhatsAppProviderInterface + FakeWhatsAppProvider
- [x] MessageTemplateService com placeholders
- [x] DispatchDailyChargesAction + Schedule em `routes/console.php`
- [x] DomainServiceProvider com DI
- [x] Config `config/jmcontabil.php`
- [x] Documentação em `docs/architecture/`
- [x] Modelo de dados evoluído (Charge em vez de só payment_notifications)
- [x] Testes unitários dos Value Objects e Template Service (8 passing)

## Próxima etapa (após confirmação)

**Etapa 2 — Criar banco:** migrations para `offices`, `customers`, `charges`, `charge_payment_methods`, `charge_deliveries`, `settings`.
