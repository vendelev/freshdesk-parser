<?php

declare(strict_types=1);

namespace Tests\Suite\Task\Infrastructure\Adapter;

use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Parser\Task\Infrastructure\Adapter\FreshdeskApiClient;
use Parser\Task\Domain\Exception\FreshdeskApiException;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class FreshdeskApiClientTest extends TestCase
{
    private MockObject&ClientInterface $httpClient;

    private FreshdeskApiClient $freshdeskApiClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->freshdeskApiClient = new FreshdeskApiClient(
            $this->httpClient,
            'test-domain',
            'test-api-key'
        );
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testGetTaskByIdReturnsTaskData(): void
    {
        // Arrange
        $taskId = 5000;
        $expectedData = [
            'id' => $taskId,
            'subject' => 'Test Task',
            'status' => 'open',
            'priority' => 'high',
        ];

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($expectedData));

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "https://test-domain.freshdesk.com/api/v2/tickets/{$taskId}")
            ->willReturn($response);

        // Act
        $result = $this->freshdeskApiClient->getTaskById($taskId);

        // Assert
        self::assertSame($expectedData, $result);
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testGetTaskByIdThrowsFreshdeskApiExceptionWhenGuzzleExceptionOccurs(): void
    {
        // Arrange
        $taskId = 5000;

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "https://test-domain.freshdesk.com/api/v2/tickets/{$taskId}")
            ->willThrowException(new RequestException(
                'API error',
                $this->createMock(RequestInterface::class)
            ));

        // Assert
        $this->expectException(FreshdeskApiException::class);

        // Act
        $this->freshdeskApiClient->getTaskById($taskId);
    }


    /**
     * @throws FreshdeskApiException
     */
    public function testGetTaskByIdThrowsFreshdeskApiExceptionWhenUnauthorized(): void
    {
        // Arrange
        $taskId = 5000;

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(401);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "https://test-domain.freshdesk.com/api/v2/tickets/{$taskId}")
            ->willReturn($response);

        // Assert
        $this->expectException(FreshdeskApiException::class);
        $this->expectExceptionMessage('Freshdesk API error (401): Unauthorized: Invalid API key');

        // Act
        $this->freshdeskApiClient->getTaskById($taskId);
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testGetTaskByIdThrowsFreshdeskApiExceptionWhenTaskNotFound(): void
    {
        // Arrange
        $taskId = 5000;

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(404);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "https://test-domain.freshdesk.com/api/v2/tickets/{$taskId}")
            ->willReturn($response);

        // Assert
        self::expectException(FreshdeskApiException::class);
        self::expectExceptionMessage('Freshdesk API error (404): Task not found');

        // Act
        $this->freshdeskApiClient->getTaskById($taskId);
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testGetTaskByIdThrowsFreshdeskApiExceptionWhenRateLimitExceeded(): void
    {
        // Arrange
        $taskId = 5000;

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(429);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "https://test-domain.freshdesk.com/api/v2/tickets/{$taskId}")
            ->willReturn($response);

        // Assert
        self::expectException(FreshdeskApiException::class);
        self::expectExceptionMessage('Freshdesk API error (429): Rate limit exceeded');

        // Act
        $this->freshdeskApiClient->getTaskById($taskId);
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testGetTaskByIdThrowsFreshdeskApiExceptionWhenJsonIsInvalid(): void
    {
        // Arrange
        $taskId = 5000;

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn('{ invalid json }');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "https://test-domain.freshdesk.com/api/v2/tickets/{$taskId}")
            ->willReturn($response);

        // Assert
        self::expectException(FreshdeskApiException::class);

        // Act
        $this->freshdeskApiClient->getTaskById($taskId);
    }
}
