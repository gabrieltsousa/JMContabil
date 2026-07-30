<?php

declare(strict_types=1);

namespace App\Application\Actions;

/**
 * Contrato base para Actions (casos de uso).
 * Controllers e Scheduler apenas disparam Actions — nunca contêm regra.
 *
 * @template TResult
 */
interface ActionInterface
{
    /**
     * @return TResult
     */
    public function execute(mixed ...$args): mixed;
}
