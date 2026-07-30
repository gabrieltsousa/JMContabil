<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clientes do escritório.
 * monthly_value armazenado em centavos (inteiro) — alinhado ao VO Money.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('office_id')
                ->nullable()
                ->constrained('offices')
                ->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('pix_key', 77);
            $table->unsignedBigInteger('monthly_value');
            $table->unsignedTinyInteger('due_day');
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index('phone');
            $table->index('status');
            $table->index('due_day');
            $table->index(['office_id', 'status', 'due_day'], 'customers_office_status_due_day_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
