<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Request;

/**
 * @final
 * @readonly
 */
final readonly class ImportTasksFromJsonRequest
{
    public function __construct(
        public string $directoryPath,
    ) {
    }
}