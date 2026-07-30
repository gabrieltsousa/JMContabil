<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cobrança mensal por competência (YYYY-MM).
 * Substitui o conceito simples de payment_notifications.
 * amount é snapshot do valor no momento da geração (centavos).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('charges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('office_id')
                ->nullable()
                ->constrained('offices')
                ->nullOnDelete();
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();
            $table->char('reference_month', 7);
            $table->unsignedBigInteger('amount');
            $table->string('status', 20)->default('pending');
            $table->date('due_date');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('message_sent')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'reference_month'], 'charges_customer_reference_unique');
            $table->index('status');
            $table->index('due_date');
            $table->index('sent_at');
            $table->index(['office_id', 'status'], 'charges_office_status_index');
            $table->index(['office_id', 'reference_month'], 'charges_office_reference_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charges');
    }
};
