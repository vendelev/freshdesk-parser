<?php

declare(strict_types=1);

namespace Tests\Suite\Task\Application\UseCase;

use Parser\Task\Application\UseCase\ParseSingleTaskFromFreshdesk;
use Parser\Task\Domain\Request\ParseSingleTaskRequest;
use Parser\Task\Domain\Response\ParseSingleTaskResponse;
use Parser\Task\Domain\TaskParserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ParseSingleTaskFromFreshdeskTest extends TestCase
{
    private ParseSingleTaskFromFreshdesk $parseSingleTaskFromFreshdesk;

    private TaskParserInterface&MockObject $taskParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taskParser = $this->createMock(TaskParserInterface::class);
        $this->parseSingleTaskFromFreshdesk = new ParseSingleTaskFromFreshdesk($this->taskParser);
    }

    public function testExecuteReturnsParseSingleTaskResponse(): void
    {
        $request = new ParseSingleTaskRequest(taskId: 123);
        $expectedResponse = new ParseSingleTaskResponse(
            taskId: 123,
            filePath: '/storage/freshdesk/freshdesk/tasks/123.json',
            message: 'Successfully parsed task #123'
        );

        $this->taskParser->expects($this->once())
            ->method('parseSingle')
            ->with($request)
            ->willReturn($expectedResponse);

        $response = $this->parseSingleTaskFromFreshdesk->execute($request);

        self::assertSame($expectedResponse, $response);
        self::assertSame(123, $response->taskId);
        self::assertStringContainsString('123', $response->filePath);
        self::assertStringContainsString('123', $response->message);
    }
}
