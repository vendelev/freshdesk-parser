<?php

declare(strict_types=1);

namespace Parser\Task\Domain;

use Parser\Task\Domain\Exception\FreshdeskApiException;

interface FreshdeskApiClientInterface
{
    /**
     * @throws FreshdeskApiException
     * @return array<array<string, mixed>>
     */
    public function getTasks(int $page = 1, int $perPage = 100): array;
}
