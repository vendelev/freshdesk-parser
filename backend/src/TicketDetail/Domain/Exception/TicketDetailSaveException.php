<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Domain\Exception;

use DomainException;

final class TicketDetailSaveException extends DomainException
{
    public static function saveFailed(int $ticketId, string $reason): self
    {
        return new self(sprintf('Не удалось сохранить детальную информацию о задаче с ID %d: %s', $ticketId, $reason));
    }
}
