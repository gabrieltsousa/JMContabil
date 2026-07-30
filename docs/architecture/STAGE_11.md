# Etapa 11 — Checklist de entrega

## Entregue

- [x] Vue 3 + Vite + TypeScript
- [x] Tailwind CSS v4 + tema próprio (teal/slate, DM Sans + Source Serif 4)
- [x] Componentes estilo shadcn/vue (Button, Input, Card, Badge, Label, Textarea)
- [x] Vue Router + Pinia + Axios (Sanctum Bearer)
- [x] Telas: Login, Dashboard, Clientes (lista/novo/editar), Histórico, Configurações
- [x] SPA Blade (`resources/views/app.blade.php`) + catch-all web
- [x] Build de produção (`npm run build`) OK

## Visual

- Branding **JM Contábil** hero no login e sidebar
- Dashboard com métricas da API
- Sem tema purple/cream genérico de AI

## Como rodar

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev

# Worker (cobranças)
php artisan queue:work --queue=charges,whatsapp,default
```

Acesse `http://127.0.0.1:8000/login`  
Credenciais: `admin@jmcontabil.test` / `password`

## Próxima etapa

**Etapa 12 — Testes** (reforçar cobertura Feature/Unit já existente + ajustar gaps).
