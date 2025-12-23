<?php

declare(strict_types=1);

namespace Tests\Suite\Ticket\Application\Query;

use InvalidArgumentException;
use Parser\Ticket\Application\Query\GetTicketQuery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для запроса на получение полных данных одной задачи.
 */
#[CoversClass(GetTicketQuery::class)]
final class GetTicketQueryTest extends TestCase
{
    /**
     * Тест создания запроса с корректным Freshdesk ID.
     */
    public function testQueryCreationWithValidId(): void
    {
        $freshdeskId = 12345;
        $query = new GetTicketQuery($freshdeskId);

        self::assertSame($freshdeskId, $query->getFreshdeskId());
    }

    /**
     * Тест создания запроса с нулевым ID выбрасывает исключение.
     */
    public function testQueryCreationWithZeroIdThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Freshdesk ID должен быть больше нуля');

        new GetTicketQuery(0);
    }

    /**
     * Тест создания запроса с отрицательным ID выбрасывает исключение.
     */
    public function testQueryCreationWithNegativeIdThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Freshdesk ID должен быть больше нуля');

        new GetTicketQuery(-123);
    }

    /**
     * Тест создания запроса с большим ID.
     */
    public function testQueryCreationWithLargeId(): void
    {
        $freshdeskId = 999999999;
        $query = new GetTicketQuery($freshdeskId);

        self::assertSame($freshdeskId, $query->getFreshdeskId());
    }
}
