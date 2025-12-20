<?php

declare(strict_types=1);

namespace Parser\Task\Application\UseCase;

use Parser\Task\Domain\Request\ParseTaskRequest;
use Parser\Task\Domain\TaskParserInterface;

/**
 * @final
 * @readonly
 */
final readonly class ParseTask
{
    public function __construct(
        private TaskParserInterface $taskParser,
    ) {
    }

    public function run(ParseTaskRequest $request): void
    {
        // Бизнес-логика парсинга задач
        // Используем $this->taskParser для парсинга данных
    }
}