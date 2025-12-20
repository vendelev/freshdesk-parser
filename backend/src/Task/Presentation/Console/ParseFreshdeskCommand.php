<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Console;

use Illuminate\Console\Command;
use Parser\Task\Application\Service\ParseTasksService;

final class ParseFreshdeskCommand extends Command
{
    protected $signature = 'freshdesk:parse';

    protected $description = 'Parse tasks from Freshdesk and save to JSON files';

    public function __construct(
        private readonly ParseTasksService $parseTasksService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting to parse Freshdesk tasks...');

        try {
            $result = $this->parseTasksService->parseAndSave();

            $this->info("Successfully parsed {$result->getCount()} tasks from Freshdesk");
            $this->info('Tasks saved to storage/freshdesk/{YYYY}/{MM}/{DD}_{HHmm}.json');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to parse Freshdesk tasks: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
