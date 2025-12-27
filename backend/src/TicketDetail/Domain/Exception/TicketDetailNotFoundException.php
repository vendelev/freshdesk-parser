<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Domain\Exception;

use DomainException;

final class TicketDetailNotFoundException extends DomainException
{
    public static function ticketNotFound(int $ticketId): self
    {
        return new self(sprintf('Детальная информация о задаче с ID %d не найдена', $ticketId));
    }
}
