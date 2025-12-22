<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Request;

final readonly class ParseSingleTaskRequest
{
    public function __construct(
        public int $taskId,
    ) {
    }
}
