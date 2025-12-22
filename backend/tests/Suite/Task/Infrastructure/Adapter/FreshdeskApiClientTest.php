<?php

declare(strict_types=1);

namespace Tests\Suite\Task\Infrastructure\Adapter;

use Parser\Task\Domain\Exception\FreshdeskApiException;
use Parser\Task\Infrastructure\Adapter\FreshdeskApiClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class FreshdeskApiClientTest extends TestCase
{
    private FreshdeskApiClient $freshdeskApiClient;

    private ClientInterface&MockObject $httpClient;

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
    public function testGetTasksReturnsArray(): void
    {
        $mockTasks = [
            ['id' => 1, 'title' => 'Test Task 1'],
            ['id' => 2, 'title' => 'Test Task 2'],
        ];

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn(json_encode($mockTasks));

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://test-domain.freshdesk.com/api/v2/tickets',
                self::callback(static fn(array $options): bool => isset($options['headers']['Authorization'])
                    && isset($options['query']['page'])
                    && isset($options['query']['per_page']))
            )
            ->willReturn($response);

        $result = $this->freshdeskApiClient->getTasks(1, 2);

        self::assertEquals($mockTasks, $result);
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testGetTasksThrowsExceptionOnHttpError(): void
    {
        $request = new Request('GET', 'https://test-domain.freshdesk.com/api/v2/tickets');
        $response = new Response(401, [], 'Unauthorized');

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://test-domain.freshdesk.com/api/v2/tickets',
                self::isArray()
            )
            ->willThrowException(new RequestException('Unauthorized', $request, $response));

        self::expectException(FreshdeskApiException::class);
        self::expectExceptionMessage('Freshdesk API error (401): Unauthorized');

        $this->freshdeskApiClient->getTasks(1, 2);
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testGetTasksThrowsExceptionOnJsonError(): void
    {
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
            ->with(self::anything(), self::anything(), self::isArray())
            ->willReturn($response);

        $this->expectException(FreshdeskApiException::class);
        $this->expectExceptionMessage('JSON parsing error: ');

        $this->freshdeskApiClient->getTasks(1, 2);
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testGetTaskReturnsRawJsonString(): void
    {
        $rawJson = '{"id":12345,"subject":"Hello"}';

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn($rawJson);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($stream);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://test-domain.freshdesk.com/api/v2/tickets/12345',
                self::callback(static fn(array $options): bool => isset($options['headers']['Authorization']))
            )
            ->willReturn($response);

        $result = $this->freshdeskApiClient->getTask(12345);

        self::assertSame($rawJson, $result);
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testGetTaskRetriesOn429ThenSucceeds(): void
    {
        $taskId = 12345;
        $url = "https://test-domain.freshdesk.com/api/v2/tickets/{$taskId}";

        $request = new Request('GET', $url);
        $rateLimitResponse = new Response(429, ['Retry-After' => '0'], 'Rate limited');
        $successResponse = new Response(200, [], '{"id":12345}');

        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->with('GET', $url, self::isArray())
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new RequestException('Rate limited', $request, $rateLimitResponse)),
                $successResponse,
            );

        $result = $this->freshdeskApiClient->getTask($taskId);

        self::assertSame('{"id":12345}', $result);
    }

    /**
     * @throws FreshdeskApiException
     */
    public function testGetTaskThrowsExceptionOn404(): void
    {
        $taskId = 99999;
        $url = "https://test-domain.freshdesk.com/api/v2/tickets/{$taskId}";
        $request = new Request('GET', $url);
        $response = new Response(404, [], 'Not Found');

        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', $url, self::isArray())
            ->willThrowException(new RequestException('Not Found', $request, $response));

        self::expectException(FreshdeskApiException::class);
        self::expectExceptionMessage('Freshdesk API error (404): Not Found');

        $this->freshdeskApiClient->getTask($taskId);
    }
}
