<?php

declare(strict_types=1);

namespace Parser\Task\Application\UseCase;

use Parser\Task\Domain\Exception\FreshdeskApiException;
use Parser\Task\Domain\FreshdeskApiClientInterface;
use Parser\Task\Domain\Request\ParseSingleTaskRequest;
use Parser\Task\Domain\Response\ParseSingleTaskResponse;

final readonly class ParseSingleTaskFromFreshdesk
{
    public function __construct(
        private FreshdeskApiClientInterface $freshdeskClient,
        private string $storagePath,
    ) {
    }

    /**
     * @throws FreshdeskApiException
     */
    public function execute(ParseSingleTaskRequest $request): ParseSingleTaskResponse
    {
        $rawJson = $this->freshdeskClient->getTask($request->taskId);

        $directory = $this->ensureStorageDirectory();
        $filename = "{$directory}/{$request->taskId}.json";

        if (file_put_contents($filename, $rawJson) === false) {
            throw new FreshdeskApiException("Failed to save task to file: {$filename}");
        }

        return new ParseSingleTaskResponse(
            taskId: $request->taskId,
            savedTo: $filename,
            message: sprintf('Successfully parsed task %d', $request->taskId),
        );
    }

    private function ensureStorageDirectory(): string
    {
        // Требуемый путь по спецификации: backend/storage/freshdesk/freshdesk/tasks/{taskId}.json
        // В рамках Laravel storage_path('freshdesk') уже указывает на backend/storage/freshdesk
        $root = rtrim($this->storagePath, '/');
        $path = "{$root}/freshdesk/tasks";

        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }

        return $path;
    }
}
