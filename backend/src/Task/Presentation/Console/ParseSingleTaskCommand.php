<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Console;

use Parser\Task\Application\UseCase\ParseSingleTaskFromFreshdesk;
use Parser\Task\Domain\Request\ParseSingleTaskRequest;
use Illuminate\Console\Command;

final class ParseSingleTaskCommand extends Command
{
    protected $signature = 'freshdesk:parse-task {taskId : The Freshdesk task ID}';

    protected $description = 'Parse a single task from Freshdesk API and save it to a file';

    public function __construct(
        private readonly ParseSingleTaskFromFreshdesk $parseSingleTaskFromFreshdesk,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $taskId = (int) $this->argument('taskId');

        $this->info("Starting to parse Freshdesk task #{$taskId}...");

        try {
            $request = new ParseSingleTaskRequest(taskId: $taskId);
            $response = $this->parseSingleTaskFromFreshdesk->execute($request);

            $this->info($response->message);
            $this->info("File saved to: {$response->filePath}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error parsing task: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
