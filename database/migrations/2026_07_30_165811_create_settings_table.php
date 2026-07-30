<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurações por escritório.
 * Unique em office_id garante 1 settings por tenant (null = default global MVP).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('office_id')
                ->nullable()
                ->constrained('offices')
                ->nullOnDelete();
            $table->string('company_name');
            $table->text('default_message');
            $table->string('whatsapp_provider', 30)->default('fake');
            $table->string('timezone', 64)->default('America/Sao_Paulo');
            $table->timestamps();

            $table->unique('office_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
