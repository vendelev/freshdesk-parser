<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Console;

use Illuminate\Console\Command;
use Parser\Task\Application\UseCase\ParseSingleTaskFromFreshdesk;
use Parser\Task\Domain\Request\ParseSingleTaskRequest;

final class ParseSingleTaskCommand extends Command
{
    protected $signature = 'freshdesk:parse-single-task {taskId : Freshdesk task (ticket) ID}';

    protected $description = 'Parse a single task (ticket) from Freshdesk API by ID';

    public function __construct(
        private readonly ParseSingleTaskFromFreshdesk $parseSingleTaskFromFreshdesk,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $taskId = (int) $this->argument('taskId');

        $this->info(sprintf('Starting Freshdesk single task parsing (taskId=%d)...', $taskId));

        try {
            $request = new ParseSingleTaskRequest(taskId: $taskId);
            $response = $this->parseSingleTaskFromFreshdesk->execute($request);

            $this->info($response->message);
            $this->info("Saved to: {$response->savedTo}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error parsing task {$taskId}: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
