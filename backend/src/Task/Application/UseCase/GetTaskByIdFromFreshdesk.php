<?php

declare(strict_types=1);

namespace Parser\Task\Application\UseCase;

use Parser\Task\Domain\FreshdeskApiClientInterface;
use Parser\Task\Domain\Request\GetTaskByIdRequest;
use Parser\Task\Domain\Response\GetTaskByIdResponse;
use Parser\Task\Domain\Exception\FreshdeskApiException;
use Parser\Task\Domain\TaskParserInterface;

final readonly class GetTaskByIdFromFreshdesk
{
    public function __construct(
        private FreshdeskApiClientInterface $freshdeskApiClient,
        private TaskParserInterface $taskParser,
    ) {
    }

    /**
     * @throws FreshdeskApiException
     */
    public function execute(GetTaskByIdRequest $request): GetTaskByIdResponse
    {
        $taskData = $this->freshdeskApiClient->getTaskById($request->taskId);

        // Сохраняем задачу в файл через парсер
        $this->taskParser->saveSingleTaskToFile($taskData, $request->taskId);

        return new GetTaskByIdResponse(
            taskData: $taskData,
            message: "Task with ID {$request->taskId} successfully retrieved from Freshdesk and saved to file"
        );
    }
}
