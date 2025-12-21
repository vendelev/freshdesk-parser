<?php

declare(strict_types=1);

namespace Parser\Task\Domain;

use Parser\Task\Domain\Request\ParseTasksRequest;
use Parser\Task\Domain\Request\ParseSingleTaskRequest;
use Parser\Task\Domain\Response\ParseTasksResponse;
use Parser\Task\Domain\Response\ParseSingleTaskResponse;

interface TaskParserInterface
{
    public function parse(ParseTasksRequest $request): ParseTasksResponse;

    public function parseSingle(ParseSingleTaskRequest $request): ParseSingleTaskResponse;
}
