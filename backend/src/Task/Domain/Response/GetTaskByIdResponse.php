<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Response;

final readonly class GetTaskByIdResponse
{
    /**
     * @param array<string, mixed> $taskData
     */
    public function __construct(
        public array $taskData,
        public string $message,
    ) {
    }
}
