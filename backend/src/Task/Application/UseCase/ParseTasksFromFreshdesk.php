<?php

declare(strict_types=1);

namespace Parser\Task\Application\UseCase;

use Parser\Task\Domain\Request\ParseTasksRequest;
use Parser\Task\Domain\Response\ParseTasksResponse;
use Parser\Task\Domain\TaskParserInterface;

final readonly class ParseTasksFromFreshdesk
{
    public function __construct(
        private TaskParserInterface $taskParser,
    ) {
    }

    public function execute(ParseTasksRequest $request): ParseTasksResponse
    {
        return $this->taskParser->parse($request);
    }
}
