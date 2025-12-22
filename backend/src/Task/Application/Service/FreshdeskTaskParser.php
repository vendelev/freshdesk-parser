<?php

declare(strict_types=1);

namespace Parser\Task\Application\Service;

use Parser\Task\Domain\Exception\FreshdeskApiException;
use Parser\Task\Domain\FreshdeskApiClientInterface;
use Parser\Task\Domain\Request\ParseTasksRequest;
use Parser\Task\Domain\Response\ParseTasksResponse;
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
        $path = $this->storagePath;

        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }

        $taskPath = $path . '/tasks';
        if (!is_dir($taskPath) && !mkdir($taskPath, 0755, true) && !is_dir($taskPath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $taskPath));
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
        $files = glob("{$storagePath}/tasks-*.json");
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
        $files = glob("{$storagePath}/tasks-*.json");
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
        $filename = "{$storagePath}/tasks-{$page}.json";

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
     * @param array<string, mixed> $taskData
     * @throws FreshdeskApiException
     */
    public function saveSingleTaskToFile(array $taskData, int $taskId): void
    {
        $storagePath = $this->getStoragePath();
        $filename = "{$storagePath}/tasks/{$taskId}.json";

        try {
            $json = json_encode($taskData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                throw new JsonException('Failed to encode task to JSON');
            }

            if (file_put_contents($filename, $json) === false) {
                throw new FreshdeskApiException("Failed to save task to file: {$filename}");
            }
        } catch (JsonException $e) {
            throw FreshdeskApiException::fromJsonError($e->getMessage());
        }
    }
}
