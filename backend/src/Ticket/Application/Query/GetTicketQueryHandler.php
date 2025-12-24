<?php

declare(strict_types=1);

namespace Parser\Ticket\Application\Query;

use Parser\Ticket\Application\Dto\TicketApiDto;
use Parser\Ticket\Domain\FreshdeskTicketAdapterInterface;

/**
 * Handler для запроса на получение данных одной задачи из Freshdesk API.
 *
 * Координирует получение данных одной задачи через Adapter.
 */
final readonly class GetTicketQueryHandler
{
    /**
     * @param FreshdeskTicketAdapterInterface $adapter Адаптер для работы с Freshdesk API
     */
    public function __construct(
        private FreshdeskTicketAdapterInterface $adapter,
    ) {
    }

    /**
     * Обработать запрос на получение данных одной задачи.
     *
     * @param GetTicketQuery $query Запрос с ID задачи в Freshdesk
     *
     * @return TicketApiDto Данные задачи
     */
    public function __invoke(GetTicketQuery $query): TicketApiDto
    {
        return $this->adapter->getTicket($query->getFreshdeskId());
    }
}