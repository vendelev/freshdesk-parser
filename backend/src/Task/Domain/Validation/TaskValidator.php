<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Validation;

use Illuminate\Support\Facades\Validator as ValidatorFacade;

/**
 * @final
 * @readonly
 */
final readonly class TaskValidator
{
    /**
     * @param array<string, mixed> $task
     * @return array<string>
     */
    public function validate(array $task): array
    {
        $rules = [
            'id' => 'required|numeric',
            'subject' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|string',
            'priority' => 'required|numeric',
            'created_at' => 'required|date',
        ];

        $validator = ValidatorFacade::make($task, $rules);

        if ($validator->passes()) {
            return [];
        }

        $errors = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            foreach ($messages as $message) {
                $errors[] = "{$field}: {$message}";
            }
        }

        return $errors;
    }
}
