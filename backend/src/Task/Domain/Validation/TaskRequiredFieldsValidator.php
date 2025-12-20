<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Validation;

/**
 * @final
 * @readonly
 */
final readonly class TaskRequiredFieldsValidator
{
    /**
     * @param array<string, mixed> $task
     * @return array<string>
     */
    public function validate(array $task): array
    {
        $errors = [];
        $requiredFields = ['id', 'subject', 'description', 'status', 'priority', 'created_at'];

        foreach ($requiredFields as $field) {
            if (!isset($task[$field])) {
                $taskId = $task['id'] ?? 'unknown';
                $errors[] = "Missing required field '{$field}' in task ID: {$taskId}";
            }
        }

        return $errors;
    }
}