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
     * Получить детальную информацию о задаче по ID
     *
     * @param int $ticketId ID задачи
     * @return string Сырой JSON с детальной информацией задачи
     * @throws FreshdeskApiConnectionException
     * @throws FreshdeskApiRateLimitException
     * @throws FreshdeskApiUnauthorizedException
     */
    public function getTicketDetail(int $ticketId): string
    {
        $retryDelay = self::INITIAL_RETRY_DELAY;

        for ($attempt = 0; $attempt < self::MAX_RETRIES; ++$attempt) {
            try {
                $response = $this->httpClient->get(
                    sprintf('https://%s.freshdesk.com/api/v2/tickets/%d', $this->freshdeskDomain, $ticketId),
                    [
                        'headers' => [
                            'Authorization' => 'Basic ' . base64_encode("{$this->freshdeskApiKey}:X"),
                        ],
                    ],
                );

                $jsonData = $response->getBody()->getContents();

                // Проверить, есть ли пагинация conversations
                $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);

                if (isset($data['conversations']) && is_array($data['conversations'])) {
                    // Если conversations есть, но возможно не все, проверить пагинацию
                    $allConversations = $this->fetchAllConversations($ticketId);
                    if ($allConversations !== []) {
                        $data['conversations'] = $allConversations;
                        $jsonData = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                    }
                }

                return $jsonData;
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

                if ($statusCode === 404) {
                    throw new FreshdeskApiConnectionException(
                        sprintf('Задача с ID %d не найдена', $ticketId),
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
            } catch (\JsonException $e) {
                throw new FreshdeskApiConnectionException(
                    'Ошибка обработки JSON ответа от Freshdesk API',
                    0,
                    $e,
                );
            }
        }

        throw new FreshdeskApiConnectionException('Не удалось получить детальную информацию о задаче после всех попыток');
    }

    /**
     * Получить все conversations для задачи с обработкой пагинации
     *
     * @param int $ticketId ID задачи
     * @return array<int, array<string, mixed>> Массив conversations
     */
    private function fetchAllConversations(int $ticketId): array
    {
        $allConversations = [];
        $page = 1;

        while (true) {
            sleep(1); // Rate limiting

            try {
                $response = $this->httpClient->get(
                    sprintf('https://%s.freshdesk.com/api/v2/tickets/%d/conversations', $this->freshdeskDomain, $ticketId),
                    [
                        'headers' => [
                            'Authorization' => 'Basic ' . base64_encode("{$this->freshdeskApiKey}:X"),
                        ],
                        'query' => [
                            'page' => $page,
                            'per_page' => self::LIMIT_PER_PAGE,
                        ],
                    ],
                );

                $conversationsJson = $response->getBody()->getContents();
                $conversations = json_decode($conversationsJson, true, 512, JSON_THROW_ON_ERROR);

                if (empty($conversations)) {
                    break;
                }

                $allConversations = array_merge($allConversations, $conversations);
                ++$page;
            } catch (RequestException) {
                // Если ошибка при получении conversations, возвращаем пустой массив
                // чтобы не блокировать получение основной информации
                break;
            } catch (\JsonException) {
                break;
            }
        }

        return $allConversations;
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
