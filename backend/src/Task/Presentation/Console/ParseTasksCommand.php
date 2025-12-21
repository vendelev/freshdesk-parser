<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Console;

use Parser\Task\Application\UseCase\ParseTasksFromFreshdesk;
use Parser\Task\Domain\Request\ParseTasksRequest;
use Illuminate\Console\Command;

final class ParseTasksCommand extends Command
{
    protected $signature = 'freshdesk:parse-tasks {--force : Restart parsing from the first page}';

    protected $description = 'Parse tasks from Freshdesk API';

    public function __construct(
        private readonly ParseTasksFromFreshdesk $parseTasksFromFreshdesk,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting Freshdesk tasks parsing...');

        try {
            $request = new ParseTasksRequest(
                startPage: 1,
                forceRestart: $this->option('force')
            );

            $response = $this->parseTasksFromFreshdesk->execute($request);

            $this->info($response->message);
            $this->info("Total pages parsed: {$response->totalPagesParsed}");
            $this->info("Total tasks parsed: {$response->totalTasksParsed}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error parsing tasks: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
