<?php

declare(strict_types=1);

namespace App\Shared\Support;

/**
 * Constantes de placeholders de mensagem.
 * Evita magic strings espalhadas pelo código.
 */
final class MessagePlaceholders
{
    public const string NAME = 'nome';

    public const string AMOUNT = 'valor';

    public const string PIX = 'pix';

    public const string DUE_DATE = 'data';

    public const string COMPANY = 'empresa';

    public const string COMPETENCE = 'competencia';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::NAME,
            self::AMOUNT,
            self::PIX,
            self::DUE_DATE,
            self::COMPANY,
            self::COMPETENCE,
        ];
    }

    private function __construct()
    {
    }
}
