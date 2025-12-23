<?php

declare(strict_types=1);

namespace Parser\Ticket\Application\Query;

use InvalidArgumentException;

/**
 * Запрос на получение полных данных одного ticket'а из Freshdesk API.
 *
 * Используется для получения полной информации о конкретной задаче по её Freshdesk ID.
 */
final readonly class GetTicketQuery
{
    /**
     * @throws InvalidArgumentException Если freshdeskId не больше нуля
     */
    public function __construct(
        private int $freshdeskId,
    ) {
        if ($this->freshdeskId <= 0) {
            throw new InvalidArgumentException('Freshdesk ID должен быть больше нуля');
        }
    }

    /**
     * Получить ID ticket'а в Freshdesk.
     */
    public function getFreshdeskId(): int
    {
        return $this->freshdeskId;
    }
}
