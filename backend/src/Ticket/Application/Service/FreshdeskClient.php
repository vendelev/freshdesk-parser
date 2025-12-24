<?php

declare(strict_types=1);

namespace Parser\Ticket\Application\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

final readonly class FreshdeskClient
{
    public function __construct(
        private string $apiKey,
        private string $domain,
        private Client $httpClient = new Client(),
    ) {
    }

    /**
     * Получение списка задач
     *
     * @param int $page Номер страницы (начинается с 1)
     * @param int $perPage Количество задач на странице
     * @return array<string, mixed> Массив с данными о задачах и информацией пагинации
     *
     * @throws GuzzleException
     */
    public function getTicketsList(int $page, int $perPage = 100): array
    {
        $url = "https://{$this->domain}.freshdesk.com/api/v2/tickets";

        try {
            $response = $this->httpClient->get($url, [
                'auth' => [$this->apiKey, ''],
                'query' => [
                    'page' => $page,
                    'per_page' => $perPage,
                ],
            ]);

            $body = $response->getBody()->getContents();
            return json_decode($body, true) ?? [];
        } catch (GuzzleException $e) {
            Log::error('Failed to get tickets list', [
                'page' => $page,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Получение детальной информации о задаче
     *
     * @param int $ticketId ID задачи
     * @return array<string, mixed> Детальная информация о задаче
     *
     * @throws GuzzleException
     */
    public function getTicketDetails(int $ticketId): array
    {
        $url = "https://{$this->domain}.freshdesk.com/api/v2/tickets/{$ticketId}";

        try {
            $response = $this->httpClient->get($url, [
                'auth' => [$this->apiKey, ''],
            ]);

            $body = $response->getBody()->getContents();
            return json_decode($body, true) ?? [];
        } catch (GuzzleException $e) {
            Log::error('Failed to get ticket details', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
