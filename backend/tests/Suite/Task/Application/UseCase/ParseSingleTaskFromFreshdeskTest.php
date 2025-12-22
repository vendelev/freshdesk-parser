<?php

declare(strict_types=1);

namespace Tests\Suite\Task\Application\UseCase;

use Parser\Task\Application\UseCase\ParseSingleTaskFromFreshdesk;
use Parser\Task\Domain\Exception\FreshdeskApiException;
use Parser\Task\Domain\FreshdeskApiClientInterface;
use Parser\Task\Domain\Request\ParseSingleTaskRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ParseSingleTaskFromFreshdeskTest extends TestCase
{
    private FreshdeskApiClientInterface&MockObject $freshdeskClient;

    private string $storagePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->freshdeskClient = $this->createMock(FreshdeskApiClientInterface::class);
        $this->storagePath = '/tmp/' . microtime(true);
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testExecuteSavesRawJsonToExpectedFile(): void
    {
        $taskId = 12345;
        $rawJson = '{"id":12345,"subject":"Hello"}';

        $this->freshdeskClient->expects($this->once())
            ->method('getTask')
            ->with($taskId)
            ->willReturn($rawJson);

        $useCase = new ParseSingleTaskFromFreshdesk(
            freshdeskClient: $this->freshdeskClient,
            storagePath: $this->storagePath,
        );

        $response = $useCase->execute(new ParseSingleTaskRequest(taskId: $taskId));

        self::assertSame($taskId, $response->taskId);
        self::assertFileExists($response->savedTo);
        self::assertSame($rawJson, file_get_contents($response->savedTo));

        // Путь должен совпадать с требованием: {storagePath}/freshdesk/tasks/{taskId}.json
        self::assertSame(
            rtrim($this->storagePath, '/') . "/freshdesk/tasks/{$taskId}.json",
            $response->savedTo
        );
    }
}
