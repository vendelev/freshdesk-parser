<?php

declare(strict_types=1);

namespace Parser\Task\Application\Service;

use DateTimeImmutable;
use Parser\Task\Domain\Entity\Task;
use Parser\Task\Domain\FreshdeskClientInterface;
use Parser\Task\Domain\Response\TaskListResponse;

final class ParseTasksService
{
    public function __construct(
        private readonly FreshdeskClientInterface $freshdeskClient,
        private readonly TaskStorageService $storageService,
    ) {}

    /**
     * Получить все задачи из Freshdesk и сохранить их.
     */
    public function parseAndSave(): TaskListResponse
    {
        $tasks = [];
        $page = 1;
        $allTickets = [];

        while (true) {
            $response = $this->freshdeskClient->getAllTickets($page, 100);

            if (empty($response)) {
                break;
            }

            $this->storageService->save($response, new DateTimeImmutable());

            sleep(1);
            ++$page;
        }

        $response = new TaskListResponse([]);

        return $response;
    }
}
