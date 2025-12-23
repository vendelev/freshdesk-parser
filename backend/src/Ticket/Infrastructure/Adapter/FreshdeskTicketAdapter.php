<?php

declare(strict_types=1);

namespace Parser\Ticket\Infrastructure\Adapter;

use Parser\Ticket\Application\Dto\TicketListApiDto;
use Parser\Ticket\Domain\FreshdeskTicketAdapterInterface;

/**
 * Адаптер для взаимодействия с Freshdesk API.
 *
 * Реализует anti-corruption layer между Freshdesk API и Application слоем.
 * Преобразует API ответы в доменные DTO объекты.
 */
final class FreshdeskTicketAdapter implements FreshdeskTicketAdapterInterface
{
    /**
     * Получить пагинированный список задач из Freshdesk API.
     *
     * @param int $page Номер страницы (начиная с 1)
     *
     * @return TicketListApiDto Пагинированный список задач
     */
    public function getTickets(int $page = 1): TicketListApiDto
    {
        // Это будет полностью реализовано в Task 20
        // На данный момент возвращаем пустой DTO для тестирования QueryHandler
        return TicketListApiDto::fromArray([], $page, 100, 0);
    }
}
