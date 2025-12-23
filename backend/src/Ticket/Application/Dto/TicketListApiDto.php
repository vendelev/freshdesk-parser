<?php

declare(strict_types=1);

namespace Parser\Ticket\Application\Dto;

/**
 * DTO для парсинга ответа от Freshdesk API при получении списка задач.
 *
 * Представляет структуру списка задач с пагинацией, соответствующую Freshdesk API v2.
 *
 * @see https://developers.freshdesk.com/api/#list_all_tickets
 */
final readonly class TicketListApiDto
{
    /**
     * @param list<TicketApiDto> $tickets Массив задач
     * @param int $page Номер текущей страницы
     * @param int $perPage Количество элементов на странице
     * @param int $totalPages Общее количество страниц
     */
    public function __construct(
        public array $tickets,
        public int $page,
        public int $perPage,
        public int $totalPages,
    ) {
    }

    /**
     * Создает DTO из массива данных, полученных из Freshdesk API.
     *
     * @param list<array<string, mixed>> $data Массив данных из Freshdesk API
     * @param int $page Номер страницы
     * @param int $perPage Количество элементов на странице
     * @param int $totalPages Общее количество страниц
     */
    public static function fromArray(array $data, int $page, int $perPage, int $totalPages): self
    {
        $tickets = [];
        foreach ($data as $ticketData) {
            $tickets[] = TicketApiDto::fromArray($ticketData);
        }

        return new self(
            tickets: $tickets,
            page: $page,
            perPage: $perPage,
            totalPages: $totalPages,
        );
    }
}
