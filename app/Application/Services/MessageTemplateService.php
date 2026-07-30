<?php

declare(strict_types=1);

namespace App\Application\Services;

final class MessageTemplateService implements MessageTemplateServiceInterface
{
    private const array PLACEHOLDERS = [
        'nome',
        'valor',
        'pix',
        'data',
        'empresa',
        'competencia',
    ];

    /**
     * {@inheritdoc}
     */
    public function render(string $template, array $replacements): string
    {
        $search = [];
        $replace = [];

        foreach ($replacements as $key => $value) {
            $normalizedKey = trim($key, "{} \t\n\r\0\x0B");
            $search[] = '{'.$normalizedKey.'}';
            $replace[] = $value;
        }

        return str_replace($search, $replace, $template);
    }

    /**
     * {@inheritdoc}
     */
    public function availablePlaceholders(): array
    {
        return self::PLACEHOLDERS;
    }
}
