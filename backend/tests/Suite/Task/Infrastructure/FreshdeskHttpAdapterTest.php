<?php

declare(strict_types=1);

namespace Tests\Suite\Task\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Parser\Task\Infrastructure\Adapter\FreshdeskHttpAdapter;
use PHPUnit\Framework\TestCase;

final class FreshdeskHttpAdapterTest extends TestCase
{
    public function testGetTicketSuccessfully(): void
    {
        $client = $this->createMock(Client::class);

        $responseData = [
            'id' => 123,
            'subject' => 'Test ticket',
            'description' => 'Test description',
            'status' => 2,
            'priority' => 1,
        ];

        $response = $this->createMock(Response::class);
        $response->method('getBody')->willReturn(
            $this->createMock(class: 'stdClass', stubProperties: [
                'contents' => json_encode($responseData, JSON_THROW_ON_ERROR),
            ])
        );

        $client->expects($this->once())
            ->method('get')
            ->with('tickets/123')
            ->willReturn($response);

        $adapter = new FreshdeskHttpAdapter('test-api-key', 'test-domain.freshdesk.com');

        $reflection = new \ReflectionClass($adapter);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($adapter, $client);

        $result = $adapter->getTicket(123);

        $this->assertEqual(123, $result['id']);
        $this->assertEqual('Test ticket', $result['subject']);
    }
}
