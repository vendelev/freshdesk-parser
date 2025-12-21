<?php

declare(strict_types=1);

namespace Parser\Task\Application\UseCase;

use Parser\Task\Domain\Request\ParseSingleTaskRequest;
use Parser\Task\Domain\Response\ParseSingleTaskResponse;
use Parser\Task\Domain\TaskParserInterface;

final readonly class ParseSingleTaskFromFreshdesk
{
    public function __construct(
        private TaskParserInterface $taskParser,
    ) {
    }

    public function execute(ParseSingleTaskRequest $request): ParseSingleTaskResponse
    {
        return $this->taskParser->parseSingle($request);
    }
}
