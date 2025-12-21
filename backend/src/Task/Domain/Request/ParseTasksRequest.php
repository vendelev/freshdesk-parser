<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Request;

final readonly class ParseTasksRequest
{
    public function __construct(
        public int $startPage = 1,
        public bool $forceRestart = false,
    ) {
    }
}
