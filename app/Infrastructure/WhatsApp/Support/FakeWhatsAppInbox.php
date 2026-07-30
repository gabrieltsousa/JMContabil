<?php

declare(strict_types=1);

namespace App\Infrastructure\WhatsApp\Support;

/**
 * Inbox em memória do Fake provider — útil para asserts em testes.
 */
final class FakeWhatsAppInbox
{
    /**
     * @var list<array<string, mixed>>
     */
    private static array $messages = [];

    /**
     * @param  array<string, mixed>  $message
     */
    public static function record(array $message): void
    {
        self::$messages[] = $message;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return self::$messages;
    }

    public static function count(): int
    {
        return count(self::$messages);
    }

    public static function last(): ?array
    {
        if (self::$messages === []) {
            return null;
        }

        return self::$messages[array_key_last(self::$messages)];
    }

    public static function clear(): void
    {
        self::$messages = [];
    }
}
