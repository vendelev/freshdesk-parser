<?php

declare(strict_types=1);

namespace Parser\Task\Domain;

use Parser\Task\Domain\Request\GetTasksRequest;

interface TaskParserInterface
{
    public function parse(string $data): array;
    
    /**
     * @return array<array<string, mixed>>
     */
    public function getTasks(GetTasksRequest $request): array;
}