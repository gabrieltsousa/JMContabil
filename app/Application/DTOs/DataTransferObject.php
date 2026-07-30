<?php

declare(strict_types=1);

namespace App\Application\DTOs;

/**
 * Contrato mínimo para DTOs de aplicação.
 */
interface DataTransferObject
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
