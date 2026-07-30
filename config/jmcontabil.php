<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | JM Contábil — Configuração do domínio
    |--------------------------------------------------------------------------
    */

    'timezone' => env('JM_TIMEZONE', 'America/Sao_Paulo'),

    'whatsapp' => [
        'default_provider' => env('JM_WHATSAPP_PROVIDER', 'fake'),
        'queue' => env('JM_WHATSAPP_QUEUE', 'whatsapp'),
        'charges_queue' => env('JM_CHARGES_QUEUE', 'charges'),
        'max_tries' => (int) env('JM_WHATSAPP_MAX_TRIES', 3),

        'fake' => [
            /*
             * Força falha no Fake provider (útil para testar retries).
             */
            'should_fail' => (bool) env('JM_WHATSAPP_FAKE_FAIL', false),
        ],

        /*
         | Credenciais dos providers reais (preencher quando implementar).
         */
        'evolution' => [
            'base_url' => env('WHATSAPP_EVOLUTION_URL'),
            'api_key' => env('WHATSAPP_EVOLUTION_API_KEY'),
            'instance' => env('WHATSAPP_EVOLUTION_INSTANCE'),
        ],
        'zapi' => [
            'base_url' => env('WHATSAPP_ZAPI_URL'),
            'instance_id' => env('WHATSAPP_ZAPI_INSTANCE'),
            'token' => env('WHATSAPP_ZAPI_TOKEN'),
        ],
        'meta_cloud' => [
            'token' => env('WHATSAPP_META_TOKEN'),
            'phone_number_id' => env('WHATSAPP_META_PHONE_NUMBER_ID'),
        ],
        'ultramsg' => [
            'instance_id' => env('WHATSAPP_ULTRAMSG_INSTANCE'),
            'token' => env('WHATSAPP_ULTRAMSG_TOKEN'),
        ],
        '360dialog' => [
            'api_key' => env('WHATSAPP_360DIALOG_API_KEY'),
        ],
    ],

    'charges' => [
        /*
         * Horário do Scheduler (timezone de jmcontabil.timezone).
         * Scheduler apenas dispara o comando charges:dispatch-daily.
         */
        'schedule_time' => env('JM_CHARGES_SCHEDULE_TIME', '00:00'),

        /*
         * Dia máximo de vencimento aceito (evita 29–31).
         */
        'max_due_day' => 28,

        /*
         * Minutos de lock do withoutOverlapping no Scheduler.
         */
        'schedule_overlap_lock_minutes' => (int) env('JM_CHARGES_SCHEDULE_LOCK', 60),
    ],

    'message' => [
        'default_template' => <<<'TXT'
Olá {nome}.

Sua mensalidade referente aos serviços contábeis venceu hoje.

Valor: {valor}

PIX (Chave Aleatória):
{pix}

Vencimento: {data}

Após realizar o pagamento, basta responder esta mensagem com o comprovante.

Obrigado.
TXT,
    ],

    /*
    |--------------------------------------------------------------------------
    | Multiempresa (preparação)
    |--------------------------------------------------------------------------
    | No MVP single-tenant, office_id permanece null.
    | Ativar multi_office=true quando houver tabela offices + auth por office.
    */
    'multi_office' => (bool) env('JM_MULTI_OFFICE', false),
];
