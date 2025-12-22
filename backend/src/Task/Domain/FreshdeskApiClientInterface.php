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

    /**
     * Получить одну задачу (ticket) из Freshdesk по ID.
     *
     * Возвращается исходный JSON-ответ API без модификаций, чтобы его можно было
     * сохранить в файл в точности как вернул Freshdesk.
     *
     * @throws FreshdeskApiException
     */
    public function getTask(int $taskId): string;
}
