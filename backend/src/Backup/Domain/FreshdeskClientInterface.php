<?php

declare(strict_types=1);

namespace Parser\Backup\Domain;

use Generator;
use Parser\Backup\Domain\Exception\FreshdeskApiConnectionException;
use Parser\Backup\Domain\Exception\FreshdeskApiRateLimitException;
use Parser\Backup\Domain\Exception\FreshdeskApiUnauthorizedException;

interface FreshdeskClientInterface
{
    /**
     * @return Generator<int, string, null, null> Генератор сырых JSON строк с задачами
     * @throws FreshdeskApiConnectionException
     * @throws FreshdeskApiRateLimitException
     * @throws FreshdeskApiUnauthorizedException
     */
    public function getTicketsIterator(): Generator;
}
