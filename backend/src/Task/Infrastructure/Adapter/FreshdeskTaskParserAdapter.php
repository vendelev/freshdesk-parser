<?php

declare(strict_types=1);

namespace Parser\Task\Infrastructure\Adapter;

use Parser\Task\Domain\TaskParserInterface;

/**
 * @final
 * @readonly
 */
final readonly class FreshdeskTaskParserAdapter implements TaskParserInterface
{
    public function __construct()
    {
    }

    public function parse(string $data): array
    {
        // Адаптер для парсинга данных из Freshdesk
        return [];
    }
}