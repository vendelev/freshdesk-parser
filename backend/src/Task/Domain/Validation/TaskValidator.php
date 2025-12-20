<?php

declare(strict_types=1);

namespace Parser\Task\Domain\Validation;

use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Support\Facades\Log;

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
        // Логируем исходные данные заявки перед валидацией
        Log::debug('Task validation started', [
            'original_task' => $task
        ]);
        
        // Преобразуем данные заявки перед валидацией
        $processedTask = $this->processTaskData($task);
        
        // Логируем преобразованные данные после обработки
        Log::debug('Task data processed', [
            'processed_task' => $processedTask
        ]);
        
        $rules = [
            'id' => 'required|numeric',
            'subject' => 'required|string',
            'description' => 'required|string',
            'status' => 'required|string',
            'priority' => 'required|numeric',
            'created_at' => 'required|date',
            // Дополнительные поля из Freshdesk (необязательные)
            'requester_id' => 'nullable|numeric',
            'responder_id' => 'nullable|numeric',
            'due_by' => 'nullable|date',
            'fr_due_by' => 'nullable|date',
            'is_escalated' => 'nullable|boolean',
        ];

        $validator = ValidatorFacade::make($processedTask, $rules);

        if ($validator->passes()) {
            return [];
        }

        $errors = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            foreach ($messages as $message) {
                $errors[] = "{$field}: {$message}";
            }
        }
        
        // Логируем детали ошибок валидации
        Log::debug('Task validation errors', [
            'original_task' => $task,
            'processed_task' => $processedTask,
            'errors' => $errors
        ]);

        return $errors;
    }
    
    /**
     * Обработка данных заявки перед валидацией
     *
     * @param array<string, mixed> $task
     * @return array<string, mixed>
     */
    private function processTaskData(array $task): array
    {
        // Создаем копию массива для обработки
        $processedTask = $task;
        
        // Преобразуем строковые ID в числовые
        if (isset($processedTask['id']) && is_string($processedTask['id'])) {
            // Удаляем все нечисловые символы, кроме точки и минуса
            $cleanId = preg_replace('/[^\d.-]/', '', $processedTask['id']);
            if (is_numeric($cleanId)) {
                $processedTask['id'] = (int)$cleanId;
            }
        }
        
        // Проверяем и очищаем пустые значения обязательных полей
        $requiredFields = ['subject', 'description', 'status'];
        foreach ($requiredFields as $field) {
            if (isset($processedTask[$field]) && is_string($processedTask[$field])) {
                $processedTask[$field] = trim($processedTask[$field]);
                // Если поле обязательно и пустое, устанавливаем значение по умолчанию
                if (empty($processedTask[$field])) {
                    switch ($field) {
                        case 'subject':
                            $processedTask[$field] = 'Без темы';
                            break;
                        case 'description':
                            $processedTask[$field] = 'Без описания';
                            break;
                        case 'status':
                            $processedTask[$field] = 'open';
                            break;
                    }
                }
            } elseif (!isset($processedTask[$field])) {
                // Если поле отсутствует, устанавливаем значение по умолчанию
                switch ($field) {
                    case 'subject':
                        $processedTask[$field] = 'Без темы';
                        break;
                    case 'description':
                        $processedTask[$field] = 'Без описания';
                        break;
                    case 'status':
                        $processedTask[$field] = 'open';
                        break;
                }
            }
        }
        
        // Преобразуем приоритет в числовой формат
        if (isset($processedTask['priority']) && is_string($processedTask['priority'])) {
            // Удаляем все нечисловые символы
            $cleanPriority = preg_replace('/[^\d]/', '', $processedTask['priority']);
            if (is_numeric($cleanPriority)) {
                $processedTask['priority'] = (int)$cleanPriority;
            } else {
                $processedTask['priority'] = 1; // значение по умолчанию
            }
        } elseif (!isset($processedTask['priority'])) {
            $processedTask['priority'] = 1; // значение по умолчанию
        }
        
        // Поддержка различных форматов дат
        $dateFields = ['created_at', 'due_by', 'fr_due_by'];
        foreach ($dateFields as $dateField) {
            if (isset($processedTask[$dateField])) {
                $processedTask[$dateField] = $this->processDate($processedTask[$dateField]);
            }
        }
        
        // Обработка дополнительных полей из Freshdesk
        if (isset($processedTask['requester_id']) && is_string($processedTask['requester_id'])) {
            // Преобразуем строковый ID запросчика в числовой
            $cleanRequestId = preg_replace('/[^\d]/', '', $processedTask['requester_id']);
            if (is_numeric($cleanRequestId)) {
                $processedTask['requester_id'] = (int)$cleanRequestId;
            }
        }
        
        if (isset($processedTask['responder_id']) && is_string($processedTask['responder_id'])) {
            // Преобразуем строковый ID ответственного в числовой
            $cleanResponderId = preg_replace('/[^\d]/', '', $processedTask['responder_id']);
            if (is_numeric($cleanResponderId)) {
                $processedTask['responder_id'] = (int)$cleanResponderId;
            }
        }
        
        if (isset($processedTask['is_escalated']) && is_string($processedTask['is_escalated'])) {
            // Преобразуем строковое значение в булево
            $processedTask['is_escalated'] = filter_var($processedTask['is_escalated'], FILTER_VALIDATE_BOOLEAN);
        }
        
        return $processedTask;
    }
    
    /**
     * Обработка даты в различные форматы
     *
     * @param mixed $date
     * @return string
     */
    private function processDate(mixed $date): string
    {
        if (is_numeric($date)) {
            // Unix timestamp
            return date('Y-m-d H:i:s', (int)$date);
        }
        
        if (is_string($date)) {
            // Пытаемся распознать различные форматы дат
            $formats = [
                'Y-m-d H:i:s',
                'Y-m-d\TH:i:sP', // ISO 8601
                'Y-m-d\TH:i:s\Z', // ISO 8601 UTC
                'Y-m-d H:i:s.u',
                'Y-m-d',
                'd.m.Y H:i:s',
                'd.m.Y',
                'd/m/Y H:i:s',
                'd/m/Y',
            ];
            
            foreach ($formats as $format) {
                $dateTime = \DateTime::createFromFormat($format, $date);
                if ($dateTime !== false) {
                    return $dateTime->format('Y-m-d H:i:s');
                }
            }
            
            // Если не удалось распознать формат, возвращаем исходное значение
            return $date;
        }
        
        // Для всех остальных случаев возвращаем строковое представление
        return (string)$date;
    }
}
