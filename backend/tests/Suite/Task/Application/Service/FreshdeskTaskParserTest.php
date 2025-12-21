<?php

declare(strict_types=1);

namespace Tests\Suite\Task\Application\Service;

use Parser\Task\Application\Service\FreshdeskTaskParser;
use Parser\Task\Domain\Exception\FreshdeskApiException;
use Parser\Task\Domain\FreshdeskApiClientInterface;
use Parser\Task\Domain\Request\ParseTasksRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class FreshdeskTaskParserTest extends TestCase
{
    private FreshdeskTaskParser $taskParser;

    private FreshdeskApiClientInterface&MockObject $freshdeskClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freshdeskClient = $this->createMock(FreshdeskApiClientInterface::class);
        $this->taskParser = new FreshdeskTaskParser($this->freshdeskClient, '/tmp/' . microtime(true));
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testParseReturnsParseTasksResponse(): void
    {
        $request = new ParseTasksRequest();
        $mockTasks = [
            ['id' => 1, 'title' => 'Test Task 1'],
            ['id' => 2, 'title' => 'Test Task 2'],
        ];

        $this->freshdeskClient->expects($this->once())
            ->method('getTasks')
            ->with(1, 100)
            ->willReturn($mockTasks);

        $response = $this->taskParser->parse($request);

        self::assertEquals(1, $response->totalPagesParsed);
        self::assertEquals(2, $response->totalTasksParsed);
        self::assertStringContainsString('Successfully parsed 2 tasks from 1 pages', $response->message);
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testParseHandlesEmptyTasks(): void
    {
        $request = new ParseTasksRequest();

        $this->freshdeskClient->expects($this->once())
            ->method('getTasks')
            ->with(1, 100)
            ->willReturn([]);

        $response = $this->taskParser->parse($request);

        self::assertEquals(0, $response->totalPagesParsed);
        self::assertEquals(0, $response->totalTasksParsed);
        self::assertStringContainsString('Successfully parsed 0 tasks from 0 pages', $response->message);
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testParseHandlesApiException(): void
    {
        $request = new ParseTasksRequest();

        $this->freshdeskClient->expects($this->once())
            ->method('getTasks')
            ->with(1, 100)
            ->willThrowException(FreshdeskApiException::fromHttpCode(401, 'Unauthorized'));

        $this->expectException(FreshdeskApiException::class);

        $this->taskParser->parse($request);
    }
}
