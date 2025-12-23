<?php

declare(strict_types=1);

namespace Parser\Ticket\Application\Query;

use Parser\Ticket\Application\Dto\TicketListApiDto;
use Parser\Ticket\Domain\FreshdeskTicketAdapterInterface;

/**
 * Handler для запроса на получение списка задач (tickets) из Freshdesk API.
 *
 * Координирует получение пагинированного списка задач через Adapter.
 */
final readonly class GetTicketListQueryHandler
{
    /**
     * @param FreshdeskTicketAdapterInterface $adapter Адаптер для работы с Freshdesk API
     */
    public function __construct(
        private FreshdeskTicketAdapterInterface $adapter,
    ) {
    }

    /**
     * Обработать запрос на получение списка задач.
     *
     * @param GetTicketListQuery $query Запрос с параметрами пагинации
     *
     * @return TicketListApiDto Список задач с информацией о пагинации
     */
    public function __invoke(GetTicketListQuery $query): TicketListApiDto
    {
        return $this->adapter->getTickets($query->page);
    }
}
