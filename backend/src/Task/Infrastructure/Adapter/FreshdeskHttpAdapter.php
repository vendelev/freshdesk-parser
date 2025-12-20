<?php

declare(strict_types=1);

namespace Parser\Task\Infrastructure\Adapter;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Parser\Task\Domain\FreshdeskClientInterface;

final class FreshdeskHttpAdapter implements FreshdeskClientInterface
{
    private readonly Client $client;

    private readonly string $apiKey;

    private readonly string $domain;

    public function __construct(string $apiKey, string $domain)
    {
        $this->apiKey = $apiKey;
        $this->domain = $domain;
        $this->client = new Client([
            'base_uri' => "https://{$domain}/api/v2/",
            'auth' => [$apiKey, 'X'],
            'timeout' => 30.0,
        ]);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws GuzzleException
     */
    public function getAllTickets(int $page = 1, int $perPage = 100): array
    {
        $response = $this->client->get('tickets', [
            'query' => [
                'page' => $page,
                'per_page' => $perPage,
            ],
        ]);

        $content = $response->getBody()->getContents();
        $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        return $data;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws GuzzleException
     */
    public function getTicket(int $ticketId): array
    {
        $response = $this->client->get("tickets/{$ticketId}");

        $content = $response->getBody()->getContents();
        $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);

        return $data;
    }
}
