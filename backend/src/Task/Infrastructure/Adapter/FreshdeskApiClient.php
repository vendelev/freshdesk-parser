<?php

declare(strict_types=1);

namespace Parser\Task\Infrastructure\Adapter;

use Parser\Task\Domain\Exception\FreshdeskApiException;
use Parser\Task\Domain\FreshdeskApiClientInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

final readonly class FreshdeskApiClient implements FreshdeskApiClientInterface
{
    private const int MAX_RETRIES_ON_RATE_LIMIT = 3;

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
        $url = "https://{$this->freshdeskDomain}.freshdesk.com/api/v2/tickets";

        try {
            $response = $this->requestWithRetry(
                method: 'GET',
                url: $url,
                options: [
                    'headers' => $this->getAuthHeaders(),
                    'query' => [
                        'page' => $page,
                        'per_page' => $perPage,
                    ],
                ],
            );

            return $this->parseResponse($response);
        } catch (RequestException $e) {
            throw FreshdeskApiException::fromHttpCode(
                $this->extractStatusCode($e),
                $e->getMessage()
            );
        } catch (GuzzleException $e) {
            throw FreshdeskApiException::fromHttpCode(
                (int) $e->getCode(),
                $e->getMessage()
            );
        }
    }

    /**
     * @throws FreshdeskApiException
     */
    public function getTask(int $taskId): string
    {
        $url = "https://{$this->freshdeskDomain}.freshdesk.com/api/v2/tickets/{$taskId}";

        try {
            $response = $this->requestWithRetry(
                method: 'GET',
                url: $url,
                options: [
                    'headers' => $this->getAuthHeaders(),
                ],
            );

            return (string) $response->getBody();
        } catch (RequestException $e) {
            throw FreshdeskApiException::fromHttpCode(
                $this->extractStatusCode($e),
                $e->getMessage()
            );
        } catch (GuzzleException $e) {
            throw FreshdeskApiException::fromHttpCode(
                (int) $e->getCode(),
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
     * @return array<string, string>
     */
    private function getAuthHeaders(): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode("{$this->freshdeskApiKey}:X"),
        ];
    }

    /**
     * Freshdesk при превышении лимита может ответить 429. В этом случае выдерживаем
     * паузу и повторяем запрос ограниченное количество раз.
     *
     * @param array<string, mixed> $options
     * @throws GuzzleException
     */
    private function requestWithRetry(string $method, string $url, array $options): ResponseInterface
    {
        $attempt = 0;

        while (true) {
            try {
                return $this->httpClient->request($method, $url, $options);
            } catch (RequestException $e) {
                $statusCode = $this->extractStatusCode($e);
                if ($statusCode !== 429) {
                    throw $e;
                }

                ++$attempt;
                if ($attempt > self::MAX_RETRIES_ON_RATE_LIMIT) {
                    throw $e;
                }

                $delaySeconds = $this->extractRetryAfterSeconds($e);
                sleep($delaySeconds);
            }
        }
    }

    private function extractStatusCode(RequestException $exception): int
    {
        $response = $exception->getResponse();

        if (!$response instanceof ResponseInterface) {
            return (int) $exception->getCode();
        }

        return $response->getStatusCode();
    }

    private function extractRetryAfterSeconds(RequestException $exception): int
    {
        $response = $exception->getResponse();

        if (!$response instanceof ResponseInterface) {
            return 1;
        }

        $retryAfterHeader = $response->getHeaderLine('Retry-After');
        if ($retryAfterHeader === '') {
            return 1;
        }

        $retryAfter = (int) $retryAfterHeader;
        if ($retryAfter < 0) {
            return 1;
        }

        return $retryAfter;
    }
}
