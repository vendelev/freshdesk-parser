<?php

declare(strict_types=1);

namespace Parser\Ticket\Application\Query;

/**
 * Запрос на получение списка задач (tickets) из Freshdesk API.
 *
 * Используется для получения пагинированного списка задач
 * с минимальной информацией (ID и др. метаданные).
 */
final readonly class GetTicketListQuery
{
    /**
     * @param int $page Номер страницы (начиная с 1)
     */
    public function __construct(
        public int $page = 1,
    ) {
    }
}
