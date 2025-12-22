<?php

declare(strict_types=1);

namespace Parser\Task\Domain;

use Parser\Task\Domain\Request\ParseTasksRequest;
use Parser\Task\Domain\Response\ParseTasksResponse;

interface TaskParserInterface
{
    public function parse(ParseTasksRequest $request): ParseTasksResponse;

    /**
     * @param array<string, mixed> $taskData
     */
    public function saveSingleTaskToFile(array $taskData, int $taskId): void;
}
