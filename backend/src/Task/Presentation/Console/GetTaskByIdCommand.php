<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Console;

use Parser\Task\Application\UseCase\GetTaskByIdFromFreshdesk;
use Parser\Task\Domain\Request\GetTaskByIdRequest;
use Illuminate\Console\Command;

final class GetTaskByIdCommand extends Command
{
    protected $signature = 'freshdesk:get-task {id : The ID of the task to retrieve}';

    protected $description = 'Get a single task from Freshdesk by ID';

    public function __construct(
        private readonly GetTaskByIdFromFreshdesk $getTaskByIdFromFreshdesk,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Retrieving task from Freshdesk...');

        try {
            $taskId = (int) $this->argument('id');
            $request = new GetTaskByIdRequest(taskId: $taskId);

            $response = $this->getTaskByIdFromFreshdesk->execute($request);

            $this->info($response->message);

            // Выводим некоторые данные задачи
            $taskData = $response->taskData;
            $this->line("Task ID: {$taskData['id']}");
            $this->line("Subject: {$taskData['subject']}");
            $this->line("Status: {$taskData['status']}");
            $this->line("Priority: {$taskData['priority']}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error retrieving task: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
