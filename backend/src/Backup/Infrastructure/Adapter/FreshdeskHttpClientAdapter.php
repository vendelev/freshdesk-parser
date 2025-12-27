<?php

declare(strict_types=1);

namespace Parser\Backup\Infrastructure\Adapter;

use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Parser\Backup\Domain\Exception\FreshdeskApiConnectionException;
use Parser\Backup\Domain\Exception\FreshdeskApiRateLimitException;
use Parser\Backup\Domain\Exception\FreshdeskApiUnauthorizedException;
use Parser\Backup\Domain\FreshdeskClientInterface;

final readonly class FreshdeskHttpClientAdapter implements FreshdeskClientInterface
{
    private const int LIMIT_PER_PAGE = 100;

    private const int MAX_RETRIES = 3;

    private const float INITIAL_RETRY_DELAY = 1.0;

    private const float RETRY_BACKOFF = 2.0;

    public function __construct(
        private Client $httpClient,
        private string $freshdeskApiKey,
        private string $freshdeskDomain,
    ) {
    }

    /**
     * @return Generator<int, string, null, null> Генератор сырых JSON строк с задачами
     * @throws FreshdeskApiConnectionException
     * @throws FreshdeskApiRateLimitException
     * @throws FreshdeskApiUnauthorizedException
     */
    public function getTicketsIterator(): Generator
    {
        $page = 1;

        while (true) {
            sleep(1);

            $ticketsJson = $this->fetchTicketsPageJson($page);

            if ($ticketsJson === null || $ticketsJson === '[]') {
                return null;
            }

            yield $ticketsJson;
            ++$page;
        }
    }

    /**
     * @param int $page Номер страницы
     * @return string|null Сырая JSON строка с задачами на странице или null если страниц больше нет
     * @throws FreshdeskApiConnectionException При ошибке подключения
     * @throws FreshdeskApiUnauthorizedException При проблеме с авторизацией
     * @throws FreshdeskApiRateLimitException При превышении лимита запросов
     */
    private function fetchTicketsPageJson(int $page): ?string
    {
        $retryDelay = self::INITIAL_RETRY_DELAY;

        for ($attempt = 0; $attempt < self::MAX_RETRIES; ++$attempt) {
            try {
                $response = $this->httpClient->get(
                    sprintf('https://%s.freshdesk.com/api/v2/tickets', $this->freshdeskDomain),
                    [
                        'headers' => [
                            'Authorization' => 'Basic ' . base64_encode("{$this->freshdeskApiKey}:X"),
                        ],
                        'query' => [
                            'page' => $page,
                            'per_page' => self::LIMIT_PER_PAGE,
                            'order_by' => 'created_at',
                            'order_type' => 'desc',
                            'updated_since' => '2005-01-01T02:00:00Z',
                        ],
                    ],
                );

                return $response->getBody()->getContents();
            } catch (ConnectException $e) {
                if ($attempt === self::MAX_RETRIES - 1) {
                    throw new FreshdeskApiConnectionException(
                        'Не удалось подключиться к Freshdesk API',
                        0,
                        $e,
                    );
                }

                sleep((int)$retryDelay);
                $retryDelay *= self::RETRY_BACKOFF;
            } catch (RequestException $e) {
                $statusCode = $e->getResponse()?->getStatusCode();

                if ($statusCode === 401) {
                    throw new FreshdeskApiUnauthorizedException(
                        'Неверный API ключ или домен Freshdesk',
                        0,
                        $e,
                    );
                }

                if ($statusCode === 429) {
                    if ($attempt === self::MAX_RETRIES - 1) {
                        throw new FreshdeskApiRateLimitException(
                            'Превышен лимит запросов к Freshdesk API',
                            0,
                            $e,
                        );
                    }

                    sleep((int)$retryDelay);
                    $retryDelay *= self::RETRY_BACKOFF;
                    continue;
                }

                throw new FreshdeskApiConnectionException(
                    sprintf('Ошибка при запросе к Freshdesk API: %s', $e->getMessage()),
                    0,
                    $e,
                );
            }
        }

        return null;
    }
}
