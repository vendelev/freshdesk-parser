<?php

declare(strict_types=1);

namespace Tests\Suite\Task\Application\UseCase;

use Parser\Task\Application\UseCase\ParseTasksFromFreshdesk;
use Parser\Task\Domain\Request\ParseTasksRequest;
use Parser\Task\Domain\Response\ParseTasksResponse;
use Parser\Task\Domain\TaskParserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ParseTasksFromFreshdeskTest extends TestCase
{
    private ParseTasksFromFreshdesk $parseTasksFromFreshdesk;

    private TaskParserInterface&MockObject $taskParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taskParser = $this->createMock(TaskParserInterface::class);
        $this->parseTasksFromFreshdesk = new ParseTasksFromFreshdesk($this->taskParser);
    }

    public function testExecuteReturnsParseTasksResponse(): void
    {
        $request = new ParseTasksRequest();
        $expectedResponse = new ParseTasksResponse(1, 100, 'Successfully parsed 100 tasks from 1 pages');

        $this->taskParser->expects($this->once())
            ->method('parse')
            ->with($request)
            ->willReturn($expectedResponse);

        $response = $this->parseTasksFromFreshdesk->execute($request);

        self::assertSame($expectedResponse, $response);
    }
}
