<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Cria/conecta instância Evolution e exibe dados do QR Code.
 */
final class EvolutionSetupCommand extends Command
{
    protected $signature = 'whatsapp:evolution-setup
                            {--name= : Nome da instância (default: config)}
                            {--force : Recria se já existir}';

    protected $description = 'Configura instância Evolution API e mostra URL/QR para parear o WhatsApp';

    public function handle(): int
    {
        $baseUrl = rtrim((string) config('jmcontabil.whatsapp.evolution.base_url'), '/');
        $apiKey = (string) config('jmcontabil.whatsapp.evolution.api_key');
        $instance = (string) ($this->option('name') ?: config('jmcontabil.whatsapp.evolution.instance'));

        if ($baseUrl === '' || $apiKey === '' || $instance === '') {
            $this->error('Configure WHATSAPP_EVOLUTION_URL, WHATSAPP_EVOLUTION_API_KEY e WHATSAPP_EVOLUTION_INSTANCE no .env');

            return self::FAILURE;
        }

        $this->info("Evolution: {$baseUrl}");
        $this->info("Instância: {$instance}");

        try {
            $existing = Http::timeout(15)
                ->withHeaders(['apikey' => $apiKey])
                ->get("{$baseUrl}/instance/fetchInstances", [
                    'instanceName' => $instance,
                ]);

            $alreadyExists = $existing->successful() && ! empty($existing->json());

            if ($alreadyExists && ! $this->option('force')) {
                $this->warn('Instância já existe. Buscando QR/status...');
            } else {
                if ($alreadyExists && $this->option('force')) {
                    Http::timeout(15)
                        ->withHeaders(['apikey' => $apiKey])
                        ->delete("{$baseUrl}/instance/delete/{$instance}");
                    $this->line('Instância anterior removida.');
                }

                $create = Http::timeout(30)
                    ->withHeaders([
                        'apikey' => $apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post("{$baseUrl}/instance/create", [
                        'instanceName' => $instance,
                        'integration' => 'WHATSAPP-BAILEYS',
                        'qrcode' => true,
                    ]);

                if (! $create->successful()) {
                    $this->error('Falha ao criar instância: '.$create->body());

                    return self::FAILURE;
                }

                $this->info('Instância criada.');
                $qrFromCreate = $create->json('qrcode.base64') ?? $create->json('base64');
                if (is_string($qrFromCreate) && $qrFromCreate !== '') {
                    $this->line('QR (base64) retornado na criação — escaneie no WhatsApp > Aparelhos conectados.');
                    $this->saveQr($qrFromCreate);
                }
            }

            $connect = Http::timeout(30)
                ->withHeaders(['apikey' => $apiKey])
                ->get("{$baseUrl}/instance/connect/{$instance}");

            if ($connect->successful()) {
                $base64 = $connect->json('base64')
                    ?? $connect->json('qrcode.base64')
                    ?? null;

                if (is_string($base64) && $base64 !== '') {
                    $this->saveQr($base64);
                }

                $this->line('Resposta connect: '.mb_substr($connect->body(), 0, 300));
            } else {
                $this->warn('Connect HTTP '.$connect->status().': '.$connect->body());
            }

            $state = Http::timeout(15)
                ->withHeaders(['apikey' => $apiKey])
                ->get("{$baseUrl}/instance/connectionState/{$instance}");

            $this->info('Estado: '.($state->json('instance.state') ?? $state->body()));

            $this->newLine();
            $this->info('Manager UI: '.$baseUrl);
            $this->info('1) Abra o manager ou o arquivo storage/app/evolution-qr.html');
            $this->info('2) Escaneie o QR com o WhatsApp do número que vai enviar cobranças');
            $this->info('3) Em Settings do JM Contábil, provider = evolution');
            $this->info('4) Dispare: php artisan charges:dispatch-daily --sync  OU  envio pela API');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function saveQr(string $base64): void
    {
        $dataUri = str_starts_with($base64, 'data:')
            ? $base64
            : 'data:image/png;base64,'.$base64;

        $html = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>QR Evolution — JM Contábil</title>
  <style>
    body { font-family: sans-serif; display:grid; place-items:center; min-height:100vh; background:#0f2a24; color:#fff; }
    img { background:#fff; padding:16px; border-radius:12px; max-width:360px; }
  </style>
</head>
<body>
  <div style="text-align:center">
    <h1>Escaneie no WhatsApp</h1>
    <p>Aparelhos conectados → Conectar um aparelho</p>
    <img src="{$dataUri}" alt="QR Code Evolution">
  </div>
</body>
</html>
HTML;

        $path = storage_path('app/evolution-qr.html');
        file_put_contents($path, $html);
        $this->info("QR salvo em: {$path}");
        $this->info('Abra no browser: file://'.$path);
    }
}
