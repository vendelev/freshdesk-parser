<?php

declare(strict_types=1);

namespace Parser\Ticket\Domain;

use Parser\Ticket\Application\Dto\TicketListApiDto;

/**
 * Интерфейс адаптера для взаимодействия с Freshdesk API.
 *
 * Предоставляет методы для получения списка задач и деталей одной задачи из Freshdesk.
 */
interface FreshdeskTicketAdapterInterface
{
    /**
     * Получить пагинированный список задач из Freshdesk API.
     *
     * @param int $page Номер страницы (начиная с 1)
     *
     * @return TicketListApiDto Пагинированный список задач
     */
    public function getTickets(int $page = 1): TicketListApiDto;
}
