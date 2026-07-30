<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Settings\Enums\WhatsAppProvider;
use App\Infrastructure\Persistence\Eloquent\Models\Office;
use App\Infrastructure\Persistence\Eloquent\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Dados iniciais do MVP (single-tenant).
 */
final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $office = Office::query()->firstOrCreate(
            ['slug' => 'jm-contabil'],
            [
                'name' => 'JM Contábil',
                'is_active' => true,
            ]
        );

        Setting::query()->updateOrCreate(
            ['office_id' => $office->id],
            [
                'company_name' => 'JM Contábil',
                'default_message' => (string) config('jmcontabil.message.default_template'),
                'whatsapp_provider' => WhatsAppProvider::Fake,
                'timezone' => 'America/Sao_Paulo',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@jmcontabil.test'],
            [
                'office_id' => $office->id,
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
