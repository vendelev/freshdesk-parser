<?php

declare(strict_types=1);

namespace Parser\Task\Application\Service;

use Parser\Task\Domain\Exception\FreshdeskApiException;
use Parser\Task\Domain\FreshdeskApiClientInterface;
use Parser\Task\Domain\Request\ParseTasksRequest;
use Parser\Task\Domain\Request\ParseSingleTaskRequest;
use Parser\Task\Domain\Response\ParseTasksResponse;
use Parser\Task\Domain\Response\ParseSingleTaskResponse;
use Parser\Task\Domain\TaskParserInterface;
use JsonException;

final readonly class FreshdeskTaskParser implements TaskParserInterface
{
    public function __construct(
        private FreshdeskApiClientInterface $freshdeskClient,
        private string $storagePath
    ) {
    }

    /**
     * @throws FreshdeskApiException
     */
    public function parse(ParseTasksRequest $request): ParseTasksResponse
    {
        $startPage = $this->determineStartPage($request);
        $totalTasks = 0;
        $pagesParsed = 0;
        $currentPage = $startPage;

        do {
            $tasks = $this->freshdeskClient->getTasks($currentPage);

            if ($tasks === []) {
                break;
            }

            $this->saveTasksToFile($tasks, $currentPage);

            $taskCount = count($tasks);
            $totalTasks += $taskCount;
            ++$pagesParsed;
            ++$currentPage;

            // Add delay between requests to respect API limits
            sleep(1);
        } while ($taskCount === 100); // Continue while we're getting full pages

        return new ParseTasksResponse(
            totalPagesParsed: $pagesParsed,
            totalTasksParsed: $totalTasks,
            message: sprintf('Successfully parsed %d tasks from %d pages', $totalTasks, $pagesParsed)
        );
    }

    private function getStoragePath(): string
    {
        // In a real Laravel application, this would be the storage path
        // For testing purposes, we'll use a temporary directory
        $path = $this->storagePath;
        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }

        if (
            !is_dir("{$path}/freshdesk")
            && !mkdir("{$path}/freshdesk", 0755, true) && !is_dir("{$path}/freshdesk")
        ) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', "{$path}/freshdesk"));
        }

        return $path;
    }

    private function determineStartPage(ParseTasksRequest $request): int
    {
        if ($request->forceRestart) {
            // Remove all existing task files when force restart is requested
            $this->cleanupExistingFiles();
            return 1;
        }

        // Find the last page that was parsed
        $lastPage = 0;
        $storagePath = $this->getStoragePath();
        $files = glob("{$storagePath}/freshdesk/tasks-*.json");
        if ($files !== false) {
            foreach ($files as $file) {
                if (preg_match('/tasks-(\d+)\.json$/', basename($file), $matches)) {
                    $page = (int) $matches[1];
                    if ($page > $lastPage) {
                        $lastPage = $page;
                    }
                }
            }
        }

        return $lastPage + 1;
    }

    private function cleanupExistingFiles(): void
    {
        $storagePath = $this->getStoragePath();
        $files = glob("{$storagePath}/freshdesk/tasks-*.json");
        if ($files !== false) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    /**
     * @param array<array<string, mixed>> $tasks
     * @throws FreshdeskApiException
     */
    private function saveTasksToFile(array $tasks, int $page): void
    {
        $storagePath = $this->getStoragePath();
        $filename = "{$storagePath}/freshdesk/tasks-{$page}.json";

        try {
            $json = json_encode($tasks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                throw new JsonException('Failed to encode tasks to JSON');
            }

            if (file_put_contents($filename, $json) === false) {
                throw new FreshdeskApiException("Failed to save tasks to file: {$filename}");
            }
        } catch (JsonException $e) {
            throw FreshdeskApiException::fromJsonError($e->getMessage());
        }
    }

    /**
     * @throws FreshdeskApiException
     */
    public function parseSingle(ParseSingleTaskRequest $request): ParseSingleTaskResponse
    {
        $task = $this->freshdeskClient->getTask($request->taskId);
        $filePath = $this->saveSingleTaskToFile($task, $request->taskId);

        return new ParseSingleTaskResponse(
            taskId: $request->taskId,
            filePath: $filePath,
            message: sprintf('Successfully parsed task #%d', $request->taskId)
        );
    }

    /**
     * @param array<string, mixed> $task
     * @throws FreshdeskApiException
     */
    private function saveSingleTaskToFile(array $task, int $taskId): string
    {
        $storagePath = $this->getStoragePath();
        $tasksDir = "{$storagePath}/freshdesk/tasks";

        if (!is_dir($tasksDir) && !mkdir($tasksDir, 0755, true) && !is_dir($tasksDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $tasksDir));
        }

        $filename = "{$tasksDir}/{$taskId}.json";

        try {
            $json = json_encode($task, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                throw new JsonException('Failed to encode task to JSON');
            }

            if (file_put_contents($filename, $json) === false) {
                throw new FreshdeskApiException("Failed to save task to file: {$filename}");
            }

            return $filename;
        } catch (JsonException $e) {
            throw FreshdeskApiException::fromJsonError($e->getMessage());
        }
    }
}
