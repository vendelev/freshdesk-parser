<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Response;

final readonly class ParseSingleTaskResponse
{
    public function __construct(
        public int $taskId,
        public string $filePath,
        public string $message,
    ) {
    }
}
