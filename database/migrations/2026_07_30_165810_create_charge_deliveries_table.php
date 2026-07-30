<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Histórico de envios por canal (WhatsApp, e-mail, SMS).
 * Suporta retries (attempt) e telemetria (duration_ms, error_message).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charge_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('charge_id')
                ->constrained('charges')
                ->cascadeOnDelete();
            $table->string('channel', 20)->default('whatsapp');
            $table->string('status', 20)->default('queued');
            $table->text('message');
            $table->string('provider', 30)->default('fake');
            $table->string('provider_message_id')->nullable();
            $table->text('provider_response')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->default(0);
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->timestamp('sent_at')->nullable();
            $table->text('whatsapp_response')->nullable();
            $table->timestamps();

            $table->index(['charge_id', 'status'], 'charge_deliveries_charge_status_index');
            $table->index(['channel', 'status'], 'charge_deliveries_channel_status_index');
            $table->index('sent_at');
            $table->index('provider_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charge_deliveries');
    }
};
