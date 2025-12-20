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

            if (empty($response['tickets'])) {
                break;
            }

            $allTickets = array_merge($allTickets, $response['tickets']);

            if (!isset($response['_links']['next'])) {
                break;
            }

            ++$page;
        }

        foreach ($allTickets as $ticket) {
            $task = new Task(
                freshdeskId: (int) $ticket['id'],
                subject: $ticket['subject'] ?? '',
                description: $ticket['description'] ?? '',
                status: $ticket['status'] ?? '',
                priority: (string) ($ticket['priority'] ?? ''),
                requesterId: (int) ($ticket['requester_id'] ?? 0),
                type: $ticket['type'] ?? '',
                source: (string) ($ticket['source'] ?? ''),
                customFields: $ticket['custom_fields'] ?? [],
            );

            $tasks[] = $task;
        }

        $response = new TaskListResponse($tasks);

        $this->storageService->save($allTickets, new DateTimeImmutable());

        return $response;
    }
}
