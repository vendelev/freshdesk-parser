<?php

declare(strict_types=1);

namespace Parser\Task\Application\UseCase;

use Parser\Task\Domain\Request\GetTasksRequest;
use Parser\Task\Domain\Request\ParseTaskRequest;
use Parser\Task\Domain\TaskParserInterface;
use Illuminate\Support\Facades\Log;

/**
 * @final
 * @readonly
 */
final readonly class ParseTask
{
    public function __construct(
        private TaskParserInterface $taskParser,
    ) {
    }

    public function run(ParseTaskRequest $request): void
    {
        // Бизнес-логика парсинга задач
        // Используем $this->taskParser для парсинга данных
    }

    /**
     * @param GetTasksRequest $request
     * @return array<array<string, mixed>>
     */
    public function getTasks(GetTasksRequest $request): array
    {
        try {
            // Получение списка задач через адаптер
            $tasks = $this->taskParser->getTasks($request);

            // Сохранение задач в файлы
            $this->saveTasksToFile($tasks);

            return $tasks;
        } catch (\Exception $e) {
            // Логирование ошибок
            Log::error('Failed to fetch and save tasks: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request,
            ]);

            throw $e;
        }
    }

    /**
     * @param array<array<string, mixed>> $tasks
     */
    private function saveTasksToFile(array $tasks): void
    {
        if (empty($tasks)) {
            return;
        }

        // Создание директории если не существует
        $year = date('Y');
        $month = date('m');
        $directory = storage_path("freshdesk/$year/$month");

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        // Сохранение каждой задачи в отдельный файл
        foreach ($tasks as $task) {
            if (isset($task['id'])) {
                $filename = "$directory/{$task['id']}.json";
                file_put_contents($filename, json_encode($task, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }
}
