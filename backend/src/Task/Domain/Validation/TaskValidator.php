<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Validation;

use Parser\Task\Domain\Validation\TaskRequiredFieldsValidator;
use Parser\Task\Domain\Validation\TaskDataFormatValidator;

/**
 * @final
 * @readonly
 */
final readonly class TaskValidator
{
    public function __construct(
        private TaskRequiredFieldsValidator $requiredFieldsValidator,
        private TaskDataFormatValidator $dataFormatValidator,
    ) {
    }

    /**
     * @param array<string, mixed> $task
     * @return array<string>
     */
    public function validate(array $task): array
    {
        $errors = [];

        // Проверка обязательных полей
        $requiredFieldErrors = $this->requiredFieldsValidator->validate($task);
        if (!empty($requiredFieldErrors)) {
            $errors = array_merge($errors, $requiredFieldErrors);
        }

        // Проверка формата данных
        $dataFormatErrors = $this->dataFormatValidator->validate($task);
        if (!empty($dataFormatErrors)) {
            $errors = array_merge($errors, $dataFormatErrors);
        }

        return $errors;
    }
}