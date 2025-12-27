<?php

declare(strict_types=1);

namespace Parser\TicketDetail\Domain;

use Parser\Backup\Domain\Exception\FreshdeskApiConnectionException;
use Parser\Backup\Domain\Exception\FreshdeskApiRateLimitException;
use Parser\Backup\Domain\Exception\FreshdeskApiUnauthorizedException;

interface FreshdeskDetailClientInterface
{
    /**
     * Получить детальную информацию о задаче по ID
     *
     * @param int $ticketId ID задачи
     * @return string Сырой JSON с детальной информацией задачи
     * @throws FreshdeskApiConnectionException
     * @throws FreshdeskApiRateLimitException
     * @throws FreshdeskApiUnauthorizedException
     */
    public function getTicketDetail(int $ticketId): string;
}
