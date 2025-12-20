<?php

declare(strict_types=1);

namespace Parser\Task\Application\Service;

use Parser\Task\Domain\TaskParserInterface;

/**
 * @final
 * @readonly
 */
final readonly class TaskParser implements TaskParserInterface
{
    public function __construct()
    {
    }

    public function parse(string $data): array
    {
        // Логика парсинга данных
        return [];
    }
}