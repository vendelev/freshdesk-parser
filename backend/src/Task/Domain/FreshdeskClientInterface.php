<?php

declare(strict_types=1);

namespace Parser\Task\Domain;

interface FreshdeskClientInterface
{
    /**
     * Получить список всех задач (tickets) из Freshdesk.
     *
     * @return array<string, mixed>
     */
    public function getAllTickets(int $page = 1, int $perPage = 100): array;
}
