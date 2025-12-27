<?php

declare(strict_types=1);

namespace Tests\Suite\Backup\Infrastructure;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Parser\Backup\Domain\Exception\FreshdeskApiConnectionException;
use Parser\Backup\Domain\Exception\FreshdeskApiRateLimitException;
use Parser\Backup\Domain\Exception\FreshdeskApiUnauthorizedException;
use Parser\Backup\Infrastructure\Adapter\FreshdeskHttpClientAdapter;
use Tests\TestCase;

final class FreshdeskHttpClientAdapterTest extends TestCase
{
    /**
     * Успешное получение одной страницы задач
     *
     * @throws FreshdeskApiConnectionException
     * @throws FreshdeskApiRateLimitException
     * @throws FreshdeskApiUnauthorizedException
     */
    public function testSuccessfulGetSinglePageOfTickets(): void
    {
        // Подготовка мока HTTP клиента
        $mockHttpClient = $this->createMock(Client::class);

        // Подготовка JSON ответа с 50 задачами
        $ticketsData = array_fill(0, 50, ['id' => 1, 'subject' => 'Test Ticket']);
        $responseBody = json_encode($ticketsData);
        self::assertIsString($responseBody);

        // Мок ответа
        $response = new Response(200, [], $responseBody);

        $mockHttpClient
            ->expects(self::any())
            ->method('get')
            ->willReturn($response);

        // Создание адаптера
        $adapter = new FreshdeskHttpClientAdapter(
            $mockHttpClient,
            'test_api_key',
            'test_domain',
        );

        // Выполнение
        $iterator = $adapter->getTicketsIterator();

        // Проверка
        $count = 0;
        foreach ($iterator as $ticketJson) {
            $decoded = json_decode($ticketJson, true);
            self::assertIsArray($decoded);
            self::assertCount(50, $decoded);
            ++$count;

            // Нужно прервать итератор, чтобы не зациклиться
            break;
        }

        self::assertGreaterThanOrEqual(1, $count);
    }

    /**
     * Пагинация (несколько страниц)
     *
     * @throws FreshdeskApiConnectionException
     * @throws FreshdeskApiRateLimitException
     * @throws FreshdeskApiUnauthorizedException
     */
    public function testGetMultiplePagesOfTickets(): void
    {
        // Подготовка мока HTTP клиента
        $mockHttpClient = $this->createMock(Client::class);

        // Подготовка ответов для трех страниц
        $page1 = array_fill(0, 100, ['id' => 1]);
        $page2 = array_fill(0, 100, ['id' => 2]);
        $page3 = array_fill(0, 50, ['id' => 3]);

        $page1Json = json_encode($page1);
        $page2Json = json_encode($page2);
        $page3Json = json_encode($page3);
        self::assertIsString($page1Json);
        self::assertIsString($page2Json);
        self::assertIsString($page3Json);

        $responses = [
            new Response(200, [], $page1Json),
            new Response(200, [], $page2Json),
            new Response(200, [], $page3Json),
            new Response(200, [], '[]'),  // Пустой ответ для завершения
        ];

        $mockHttpClient
            ->expects(self::any())
            ->method('get')
            ->willReturnOnConsecutiveCalls(...$responses);

        // Создание адаптера
        $adapter = new FreshdeskHttpClientAdapter(
            $mockHttpClient,
            'test_api_key',
            'test_domain',
        );

        // Выполнение
        $iterator = $adapter->getTicketsIterator();

        // Проверка
        $pageCount = 0;
        $totalTickets = 0;

        foreach ($iterator as $ticketJson) {
            $decoded = json_decode($ticketJson, true);
            if (is_array($decoded) && $decoded !== []) {
                $totalTickets += count($decoded);
                ++$pageCount;
            }

            // Нужно прервать после получения всех данных
            if ($pageCount >= 3) {
                break;
            }
        }

        self::assertSame(3, $pageCount);
        self::assertSame(250, $totalTickets);
    }

    /**
     * Обработка Rate Limiting (429)
     *
     * @throws FreshdeskApiConnectionException
     * @throws FreshdeskApiRateLimitException
     * @throws FreshdeskApiUnauthorizedException
     */
    public function testHandleRateLimitingError(): void
    {
        // Подготовка мока HTTP клиента
        $mockHttpClient = $this->createMock(Client::class);

        // Первый запрос вызывает 429 ошибку
        new Request('GET', 'https://test.freshdesk.com/api/v2/tickets');

        // Повторный запрос успешен
        $successResponse = new Response(200, [], (string)json_encode(array_fill(0, 50, ['id' => 1])));

        // Последовательность ответов: ошибка 429 -> успех -> пустой ответ
        $mockHttpClient
            ->expects(self::any())
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                new Response(429),
                $successResponse,
                new Response(200, [], '[]'),
            );

        // Создание адаптера
        $adapter = new FreshdeskHttpClientAdapter(
            $mockHttpClient,
            'test_api_key',
            'test_domain',
        );

        // Выполнение - итератор должен переживать ошибку 429 и повторить
        $iterator = $adapter->getTicketsIterator();

        $count = 0;
        foreach ($iterator as $ticketJson) {
            $decoded = json_decode($ticketJson, true);
            if (is_array($decoded) && $decoded !== []) {
                ++$count;
                self::assertCount(50, $decoded);
            }

            if ($count >= 1) {
                break;
            }
        }

        self::assertGreaterThanOrEqual(1, $count);
    }

    /**
     * Превышение лимита запросов с исчерпанием попыток
     *
     * @throws FreshdeskApiConnectionException
     * @throws FreshdeskApiRateLimitException
     * @throws FreshdeskApiUnauthorizedException
     */
    public function testRateLimitExceptionAfterMaxRetries(): void
    {
        // Подготовка мока HTTP клиента
        $mockHttpClient = $this->createMock(Client::class);

        // Все запросы вызывают 429 ошибку
        $request = new Request('GET', 'https://test.freshdesk.com/api/v2/tickets');
        $rateLimitException = new RequestException(
            'Too Many Requests',
            $request,
            new Response(429),
        );

        $mockHttpClient
            ->expects(self::any())
            ->method('get')
            ->willThrowException($rateLimitException);

        // Создание адаптера
        $adapter = new FreshdeskHttpClientAdapter(
            $mockHttpClient,
            'test_api_key',
            'test_domain',
        );

        // Выполнение - должно выбросить исключение после 3 попыток
        $this->expectException(FreshdeskApiRateLimitException::class);

        $iterator = $adapter->getTicketsIterator();
        foreach ($iterator as $_) {
            echo $_;
            // Итератор должен выбросить исключение при первом обращении
            break;
        }
    }

    /**
     * Ошибка подключения (ConnectException)
     *
     * @throws FreshdeskApiConnectionException
     * @throws FreshdeskApiRateLimitException
     * @throws FreshdeskApiUnauthorizedException
     */
    public function testHandleConnectionError(): void
    {
        // Подготовка мока HTTP клиента
        $mockHttpClient = $this->createMock(Client::class);

        // Генерируем ConnectException
        $request = new Request('GET', 'https://test.freshdesk.com/api/v2/tickets');
        $connectException = new ConnectException('cURL error 6: Could not resolve host', $request);

        $mockHttpClient
            ->expects(self::any())
            ->method('get')
            ->willThrowException($connectException);

        // Создание адаптера
        $adapter = new FreshdeskHttpClientAdapter(
            $mockHttpClient,
            'test_api_key',
            'test_domain',
        );

        // Выполнение - должно выбросить FreshdeskApiConnectionException
        $this->expectException(FreshdeskApiConnectionException::class);

        $iterator = $adapter->getTicketsIterator();
        foreach ($iterator as $_) {
            echo $_;
            // Итератор должен выбросить исключение при первом обращении
            break;
        }
    }

    /**
     * Ошибка авторизации (401)
     *
     * @throws FreshdeskApiConnectionException
     * @throws FreshdeskApiRateLimitException
     * @throws FreshdeskApiUnauthorizedException
     */
    public function testHandleUnauthorizedError(): void
    {
        // Подготовка мока HTTP клиента
        $mockHttpClient = $this->createMock(Client::class);

        // Генерируем RequestException с статус кодом 401
        $request = new Request('GET', 'https://test.freshdesk.com/api/v2/tickets');
        $unauthorizedException = new RequestException(
            'Unauthorized',
            $request,
            new Response(401),
        );

        $mockHttpClient
            ->expects(self::any())
            ->method('get')
            ->willThrowException($unauthorizedException);

        // Создание адаптера
        $adapter = new FreshdeskHttpClientAdapter(
            $mockHttpClient,
            'test_api_key',
            'test_domain',
        );

        // Выполнение - должно выбросить FreshdeskApiUnauthorizedException
        $this->expectException(FreshdeskApiUnauthorizedException::class);

        $iterator = $adapter->getTicketsIterator();
        foreach ($iterator as $_) {
            echo $_;
            // Итератор должен выбросить исключение при первом обращении
            break;
        }
    }

    /**
     * Проверка, что адаптер передает API ключ в заголовках
     *
     * @throws FreshdeskApiConnectionException
     * @throws FreshdeskApiRateLimitException
     * @throws FreshdeskApiUnauthorizedException
     */
    public function testApiKeyInHeaders(): void
    {
        // Подготовка мока HTTP клиента
        $mockHttpClient = $this->createMock(Client::class);

        $successResponse = new Response(200, [], (string)json_encode(array_fill(0, 50, ['id' => 1])));

        $mockHttpClient
            ->expects(self::any())
            ->method('get')
            ->with(
                self::stringContains('freshdesk.com/api/v2/tickets'),
                self::callback(static fn(array $options): bool => isset($options['headers']['Authorization']) &&
                    str_starts_with($options['headers']['Authorization'], 'Basic ')),
            )
            ->willReturn($successResponse);

        // Создание адаптера
        $adapter = new FreshdeskHttpClientAdapter(
            $mockHttpClient,
            'test_api_key',
            'test_domain',
        );

        // Выполнение
        $iterator = $adapter->getTicketsIterator();

        foreach ($iterator as $_) {
            echo $_;
            break;
        }
    }
}
