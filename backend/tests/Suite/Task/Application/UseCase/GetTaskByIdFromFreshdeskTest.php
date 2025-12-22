<?php

declare(strict_types=1);

namespace Tests\Suite\Task\Application\UseCase;

use PHPUnit\Framework\TestCase;
use Parser\Task\Application\UseCase\GetTaskByIdFromFreshdesk;
use Parser\Task\Domain\FreshdeskApiClientInterface;
use Parser\Task\Domain\Request\GetTaskByIdRequest;
use Parser\Task\Domain\Exception\FreshdeskApiException;
use Parser\Task\Domain\TaskParserInterface;

final class GetTaskByIdFromFreshdeskTest extends TestCase
{
    /**
     * @throws FreshdeskApiException
     */
    public function testExecuteReturnsGetTaskByIdResponse(): void
    {
        // Arrange
        $taskId = 5000;
        $taskData = [
            'id' => $taskId,
            'subject' => 'Test Task',
            'status' => 'open',
            'priority' => 'high',
        ];

        $freshdeskApiClient = $this->createMock(FreshdeskApiClientInterface::class);
        $freshdeskApiClient->expects($this->once())
            ->method('getTaskById')
            ->with($taskId)
            ->willReturn($taskData);

        $taskParser = $this->createMock(TaskParserInterface::class);

        $useCase = new GetTaskByIdFromFreshdesk($freshdeskApiClient, $taskParser);
        $request = new GetTaskByIdRequest($taskId);

        // Act
        $response = $useCase->execute($request);

        // Assert
        self::assertSame($taskData, $response->taskData);
        self::assertSame(
            "Task with ID {$taskId} successfully retrieved from Freshdesk and saved to file",
            $response->message
        );
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testExecuteThrowsFreshdeskApiExceptionWhenApiClientFails(): void
    {
        // Arrange
        $taskId = 5000;
        $freshdeskApiClient = $this->createMock(FreshdeskApiClientInterface::class);
        $freshdeskApiClient->expects($this->once())
            ->method('getTaskById')
            ->with($taskId)
            ->willThrowException(new FreshdeskApiException('API error'));

        $taskParser = $this->createMock(TaskParserInterface::class);

        $useCase = new GetTaskByIdFromFreshdesk($freshdeskApiClient, $taskParser);
        $request = new GetTaskByIdRequest($taskId);

        // Assert
        $this->expectException(FreshdeskApiException::class);

        // Act
        $useCase->execute($request);
    }
}
