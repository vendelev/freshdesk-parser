<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Infrastructure\Adapter;

use Parser\Backup\Domain\FreshdeskClientInterface;
use Parser\TicketDetail\Domain\FreshdeskDetailClientInterface;

final readonly class FreshdeskHttpDetailClientAdapter implements FreshdeskDetailClientInterface
{
    public function __construct(
        private FreshdeskClientInterface $freshdeskClient,
    ) {
    }

    /**
     * Получить детальную информацию о задаче по ID
     *
     * @param int $ticketId ID задачи
     * @return string Сырой JSON с детальной информацией задачи
     */
    public function getTicketDetail(int $ticketId): string
    {
        return $this->freshdeskClient->getTicketDetail($ticketId);
    }
}
