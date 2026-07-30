<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forma(s) de pagamento vinculadas à cobrança.
 * MVP: pix_key. Futuro: pix_copia_cola, qr_code, boleto.
 * payload JSON guarda dados específicos do tipo sem alterar schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charge_payment_methods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('charge_id')
                ->constrained('charges')
                ->cascadeOnDelete();
            $table->string('type', 30);
            $table->unsignedBigInteger('amount');
            $table->json('payload');
            $table->timestamps();

            $table->index(['charge_id', 'type'], 'charge_payment_methods_charge_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charge_payment_methods');
    }
};
