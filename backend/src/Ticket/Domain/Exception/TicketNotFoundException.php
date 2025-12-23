<?php

declare(strict_types=1);

namespace Parser\Ticket\Domain\Exception;

use Exception;

/**
 * Исключение, выбрасываемое когда задача не найдена.
 */
final class TicketNotFoundException extends Exception
{
    /**
     * @param int $freshdeskId Идентификатор задачи в Freshdesk
     */
    public function __construct(int $freshdeskId)
    {
        parent::__construct(sprintf('Ticket with Freshdesk ID %d not found', $freshdeskId));
    }
}
