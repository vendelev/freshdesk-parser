<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Console;

use Illuminate\Console\Command;
use Parser\Task\Application\UseCase\ParseTask;
use Parser\Task\Domain\Request\GetTasksRequest;
use Parser\Task\Domain\Request\ParseTaskRequest;

/**
 * @final
 */
final class ParseTaskCommand extends Command
{
    protected $signature = 'task:parse {--updated-since=} {--per-page=100} {--page=1}';
    protected $description = 'Parse tasks from Freshdesk';

    public function handle(ParseTask $parseTask): void
    {
        $request = new ParseTaskRequest();
        $parseTask->run($request);
        
        // Получение списка задач
        $getTasksRequest = new GetTasksRequest(
            $this->option('updated-since'),
            (int) $this->option('per-page'),
            (int) $this->option('page')
        );
        
        $tasks = $parseTask->getTasks($getTasksRequest);
        
        $this->info("Successfully fetched and saved " . count($tasks) . " tasks.");
    }
}