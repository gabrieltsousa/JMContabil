<?php

declare(strict_types=1);

namespace App\Application\Services;

/**
 * Serviço de templates com placeholders.
 *
 * Placeholders suportados no MVP:
 * {nome}, {valor}, {pix}, {data}, {empresa}, {competencia}
 */
interface MessageTemplateServiceInterface
{
    /**
     * @param  array<string, string>  $replacements
     */
    public function render(string $template, array $replacements): string;

    /**
     * @return list<string>
     */
    public function availablePlaceholders(): array;
}
