<?php

declare(strict_types=1);

namespace Parser\Ticket\Application\Factory;

use Parser\Ticket\Application\Dto\TicketApiDto;
use Parser\Ticket\Application\Service\TicketTransformer;
use Parser\Ticket\Domain\Entity\Ticket;

/**
 * Фабрика для создания сущностей задач из DTO.
 */
final readonly class TicketFactory
{
    public function __construct(
        private TicketTransformer $ticketTransformer
    ) {
    }

    /**
     * Создает сущность задачи из DTO API.
     *
     * @param TicketApiDto $dto DTO с данными задачи из Freshdesk API
     *
     * @return Ticket Доменная сущность задачи
     *
     * @throws \DateMalformedStringException Если формат даты некорректен
     */
    public function createFromApiDto(TicketApiDto $dto): Ticket
    {
        return $this->ticketTransformer->transformFromApi($dto);
    }
}
