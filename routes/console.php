<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Cobranças diárias
|--------------------------------------------------------------------------
|
| Laravel 11+ não usa mais app/Console/Kernel.php.
| O agendamento vive aqui (routes/console.php).
|
| Regra de ouro: o Scheduler NÃO contém regra de negócio.
| Apenas dispara o comando Artisan → Action → Queue → Service.
|
| Produção (crontab):
|   * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
|
*/
$schedule = Schedule::command('charges:dispatch-daily')
    ->dailyAt((string) config('jmcontabil.charges.schedule_time', '00:00'))
    ->timezone((string) config('jmcontabil.timezone', 'America/Sao_Paulo'))
    ->name('dispatch-daily-charges')
    ->withoutOverlapping(
        (int) config('jmcontabil.charges.schedule_overlap_lock_minutes', 60)
    )
    ->appendOutputTo(storage_path('logs/charges-schedule.log'));

/*
| onOneServer exige cache distribuído (Redis). Em ambientes locais com
| cache file/array o lock sem onOneServer já evita overlap no mesmo host.
*/
if (in_array(config('cache.default'), ['redis', 'dynamodb', 'database'], true)) {
    $schedule->onOneServer();
}
