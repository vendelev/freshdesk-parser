<?php

declare(strict_types=1);

namespace Parser\Task\Presentation\Console;

use Illuminate\Console\Command;
use Parser\Task\Application\Service\LoadTicketDetailsService;

final class LoadTicketDetailsCommand extends Command
{
    protected $signature = 'freshdesk:load-details';

    protected $description = 'Load ticket details from Freshdesk API and save them as individual JSON files';

    public function __construct(
        private readonly LoadTicketDetailsService $loadDetailsService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting to load ticket details from Freshdesk...');

        try {
            $results = $this->loadDetailsService->loadAndSave();

            $successful = count(array_filter($results, fn($r) => $r['status'] === 'success'));
            $failed = count(array_filter($results, fn($r) => $r['status'] === 'failed'));

            $this->info("Successfully loaded details for {$successful} tickets");

            if ($failed > 0) {
                $this->warn("Failed to load details for {$failed} tickets");

                foreach (array_filter($results, fn($r) => $r['status'] === 'failed') as $result) {
                    $this->line("  - Ticket #{$result['ticket_id']}: {$result['error']}");
                }
            }

            $this->info('Ticket details saved to storage/freshdesk/{YYYY}/{MM}/{ticket_id}.json');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Failed to load ticket details: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
