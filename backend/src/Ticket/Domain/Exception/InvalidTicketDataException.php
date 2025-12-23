<?php

declare(strict_types=1);

namespace Parser\Ticket\Domain\Exception;

use Exception;

/**
 * Исключение, выбрасываемое когда данные задачи некорректны.
 */
final class InvalidTicketDataException extends Exception
{
}
