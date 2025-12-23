<?php

declare(strict_types=1);

namespace Parser\Ticket\Domain\Response;

/**
 * DTO для выхода из UseCase со списком задач.
 *
 * Представляет список задач для передачи между слоями приложения.
 */
final readonly class TicketListResponse
{
    /**
     * @param TicketResponse[] $tickets Список задач
     */
    public function __construct(
        public array $tickets
    ) {
    }
}
