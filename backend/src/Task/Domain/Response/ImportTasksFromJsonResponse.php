<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Response;

/**
 * @final
 * @readonly
 */
final readonly class ImportTasksFromJsonResponse
{
    public function __construct(
        public int $totalTasks,
        public int $successfulTasks,
        public int $errorTasks,
        public int $duplicateTasks,
        public array $processedTaskIds,
    ) {
    }
}