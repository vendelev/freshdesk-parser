<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Response;

use Parser\Task\Domain\Entity\Task;

final class TaskListResponse
{
    /** @var Task[] */
    private array $tasks;

    /**
     * @param Task[] $tasks
     */
    public function __construct(array $tasks)
    {
        $this->tasks = $tasks;
    }

    /** @return Task[] */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function getCount(): int
    {
        return count($this->tasks);
    }

    public function toArray(): array
    {
        return array_map(static fn (Task $task) => $task->toArray(), $this->tasks);
    }
}
