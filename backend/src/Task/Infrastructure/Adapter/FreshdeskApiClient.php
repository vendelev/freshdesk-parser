<?php

declare(strict_types=1);

namespace Parser\Task\Infrastructure\Adapter;

use Parser\Task\Domain\Exception\FreshdeskApiException;
use Parser\Task\Domain\FreshdeskApiClientInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

final readonly class FreshdeskApiClient implements FreshdeskApiClientInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private string $freshdeskDomain,
        private string $freshdeskApiKey,
    ) {
    }

    /**
     * @return array<array<string, mixed>>
     *  @throws FreshdeskApiException
     */
    public function getTasks(int $page = 1, int $perPage = 100): array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                "https://{$this->freshdeskDomain}.freshdesk.com/api/v2/tickets",
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode("{$this->freshdeskApiKey}:X"),
                    ],
                    'query' => [
                        'page' => $page,
                        'per_page' => $perPage,
                    ],
                ]
            );

            return $this->parseResponse($response);
        } catch (GuzzleException $e) {
            throw FreshdeskApiException::fromHttpCode(
                $e->getCode(),
                $e->getMessage()
            );
        }
    }

    /**
     * @return array<string, mixed>
     * @throws FreshdeskApiException
     */
    public function getTaskById(int $taskId): array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                "https://{$this->freshdeskDomain}.freshdesk.com/api/v2/tickets/{$taskId}",
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode("{$this->freshdeskApiKey}:X"),
                    ],
                ]
            );

            // Проверяем код ответа и обрабатываем специфичные ошибки
            $statusCode = $response->getStatusCode();
            if ($statusCode === 401) {
                throw FreshdeskApiException::fromHttpCode(401, 'Unauthorized: Invalid API key');
            }

            if ($statusCode === 404) {
                throw FreshdeskApiException::fromHttpCode(404, 'Task not found');
            }

            if ($statusCode === 429) {
                throw FreshdeskApiException::fromHttpCode(429, 'Rate limit exceeded');
            }

            return $this->parseSingleResponse($response);
        } catch (GuzzleException $e) {
            throw FreshdeskApiException::fromHttpCode(
                $e->getCode(),
                $e->getMessage()
            );
        }
    }

    /**
     * @throws FreshdeskApiException
     */
    /**
     * @return array<array<string, mixed>>
     * @throws FreshdeskApiException
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw FreshdeskApiException::fromJsonError(json_last_error_msg());
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     * @throws FreshdeskApiException
     */
    private function parseSingleResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw FreshdeskApiException::fromJsonError(json_last_error_msg());
        }

        return $data;
    }
}
