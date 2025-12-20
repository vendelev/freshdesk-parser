<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Validation;

/**
 * @final
 * @readonly
 */
final readonly class TaskDataFormatValidator
{
    /**
     * @param array<string, mixed> $task
     * @return array<string>
     */
    public function validate(array $task): array
    {
        $errors = [];

        // Валидация ID (должен быть числом)
        if (isset($task['id']) && !is_numeric($task['id'])) {
            $errors[] = "Invalid ID format in task: {$task['id']}";
        }

        // Валидация даты создания
        if (isset($task['created_at']) && !strtotime($task['created_at'])) {
            $taskId = $task['id'] ?? 'unknown';
            $errors[] = "Invalid created_at format in task ID: {$taskId}";
        }

        // Валидация приоритета (должен быть числом)
        if (isset($task['priority']) && !is_numeric($task['priority'])) {
            $taskId = $task['id'] ?? 'unknown';
            $errors[] = "Invalid priority format in task ID: {$taskId}";
        }

        return $errors;
    }
}