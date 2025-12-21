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
            ->with('GET', 'https://test-domain.freshdesk.com/api/v2/tickets')
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
            ->with('GET', 'https://test-domain.freshdesk.com/api/v2/tickets')
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
            ->willReturn($response);

        $this->expectException(FreshdeskApiException::class);
        $this->expectExceptionMessage('JSON parsing error: ');

        $this->freshdeskApiClient->getTasks(1, 2);
    }
}
