<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Request;

/**
 * @final
 * @readonly
 */
final readonly class GetTasksRequest
{
    public function __construct(
        public ?string $updatedSince = null,
        public int $perPage = 100,
        public int $page = 1,
    ) {
    }
}