<?php

declare(strict_types=1);

namespace Parser\Task\Application\UseCase;

use Parser\Task\Domain\Request\ImportTasksFromJsonRequest;
use Parser\Task\Domain\Response\ImportTasksFromJsonResponse;
use Parser\Task\Domain\JsonFileReaderInterface;
use Parser\Task\Domain\TaskParserInterface;
use Parser\Task\Domain\Validation\TaskValidator;
use RuntimeException;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * @final
 * @readonly
 */
final readonly class ImportTasksFromJson
{
    public function __construct(
        private JsonFileReaderInterface $jsonFileReader,
        private TaskParserInterface $taskParser,
        private TaskValidator $taskValidator,
    ) {
    }

    public function run(ImportTasksFromJsonRequest $request): ImportTasksFromJsonResponse
    {
        // Поиск JSON файлов в указанной директории
        $jsonFiles = $this->jsonFileReader->findJsonFiles($request->directoryPath);
        
        $totalTasks = 0;
        $successfulTasks = 0;
        $errorTasks = 0;
        $duplicateTasks = 0;
        $processedTaskIds = [];
        
        foreach ($jsonFiles as $filePath) {
            try {
                // Чтение JSON файла
                $fileData = $this->jsonFileReader->readFile($filePath);
                
                Log::info('Processing JSON file', ['file' => $filePath, 'task_count' => count($fileData->content)]);
                
                // Обработка задач из файла
                foreach ($fileData->content as $index => $task) {
                    $totalTasks++;
                    
                    try {
                        // Логируем исходные данные заявки
                        Log::debug('Processing task', [
                            'task_index' => $index,
                            'file' => $filePath,
                            'original_task_data' => $task
                        ]);
                        
                        // Проверка дубликатов
                        if (isset($task['id']) && in_array($task['id'], $processedTaskIds, true)) {
                            $duplicateTasks++;
                            Log::warning('Duplicate task found', [
                                'task_id' => $task['id'],
                                'file' => $filePath,
                                'task_index' => $index
                            ]);
                            continue;
                        }
                        
                        // Валидация задачи
                        $errors = $this->taskValidator->validate($task);
                        if (!empty($errors)) {
                            $errorTasks++;
                            Log::error('Task validation failed with detailed errors', [
                                'task' => $task,
                                'errors' => $errors,
                                'file' => $filePath,
                                'task_index' => $index,
                                'error_count' => count($errors)
                            ]);
                            continue;
                        }
                        
                        // Добавляем ID в обработанные
                        if (isset($task['id'])) {
                            $processedTaskIds[] = $task['id'];
                        }
                        
                        // Логируем преобразованные данные после валидации
                        Log::debug('Task validated successfully', [
                            'task_id' => $task['id'] ?? null,
                            'file' => $filePath,
                            'task_index' => $index
                        ]);
                        
                        // Парсинг задачи
                        $this->taskParser->parse(json_encode($task));
                        $successfulTasks++;
                        
                        Log::debug('Task processed successfully', [
                            'task_id' => $task['id'] ?? null,
                            'file' => $filePath,
                            'task_index' => $index
                        ]);
                    } catch (Throwable $e) {
                        // Логирование ошибки для конкретной заявки и продолжение обработки следующих заявок
                        $errorTasks++;
                        Log::error('Error processing individual task', [
                            'task' => $task,
                            'file' => $filePath,
                            'task_index' => $index,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        continue;
                    }
                }
            } catch (RuntimeException $e) {
                // Логирование ошибки и продолжение обработки следующих файлов
                Log::error('Error processing JSON file', [
                    'file' => $filePath,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                continue;
            } catch (Throwable $e) {
                // Логирование неожиданной ошибки и продолжение обработки следующих файлов
                Log::error('Unexpected error processing JSON file', [
                    'file' => $filePath,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                continue;
            }
        }
        
        Log::info('Import tasks completed', [
            'total' => $totalTasks,
            'successful' => $successfulTasks,
            'errors' => $errorTasks,
            'duplicates' => $duplicateTasks,
            'success_rate' => $totalTasks > 0 ? round(($successfulTasks / $totalTasks) * 100, 2) : 0
        ]);
        
        return new ImportTasksFromJsonResponse(
            $totalTasks,
            $successfulTasks,
            $errorTasks,
            $duplicateTasks,
            $processedTaskIds,
        );
    }
}