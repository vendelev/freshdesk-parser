<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Response;

final readonly class ParseTasksResponse
{
    public function __construct(
        public int $totalPagesParsed,
        public int $totalTasksParsed,
        public string $message,
    ) {
    }
}
