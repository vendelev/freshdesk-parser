<?php

declare(strict_types=1);

namespace Parser\Task\Application\UseCase;

use Parser\Task\Domain\Request\ImportTasksFromJsonRequest;
use Parser\Task\Domain\Response\ImportTasksFromJsonResponse;
use Parser\Task\Domain\JsonFileReaderInterface;
use Parser\Task\Domain\TaskParserInterface;
use Parser\Task\Domain\Validation\TaskValidator;
use RuntimeException;

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
                
                // Обработка задач из файла
                foreach ($fileData->content as $task) {
                    $totalTasks++;
                    
                    // Проверка дубликатов
                    if (isset($task['id']) && in_array($task['id'], $processedTaskIds, true)) {
                        $duplicateTasks++;
                        continue;
                    }
                    
                    // Валидация задачи
                    $errors = $this->taskValidator->validate($task);
                    if (!empty($errors)) {
                        $errorTasks++;
                        continue;
                    }
                    
                    // Добавляем ID в обработанные
                    if (isset($task['id'])) {
                        $processedTaskIds[] = $task['id'];
                    }
                    
                    // Парсинг задачи
                    $this->taskParser->parse(json_encode($task));
                    $successfulTasks++;
                }
            } catch (RuntimeException $e) {
                // Логирование ошибки и продолжение обработки следующих файлов
                continue;
            }
        }
        
        return new ImportTasksFromJsonResponse(
            $totalTasks,
            $successfulTasks,
            $errorTasks,
            $duplicateTasks,
            $processedTaskIds,
        );
    }
}