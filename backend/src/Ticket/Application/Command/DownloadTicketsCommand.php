<?php

declare(strict_types=1);

namespace Parser\Ticket\Application\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Parser\Ticket\Application\Service\FreshdeskClient;
use Parser\Ticket\Application\Service\FileStorage;

final class DownloadTicketsCommand extends Command
{
    protected $signature = 'freshdesk:download:tickets';

    protected $description = 'Download tickets from Freshdesk API';

    public function __construct(
        private FileStorage $fileStorage,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        Log::info('Starting Freshdesk tickets download');

        $apiKey = config('freshdesk.api_key');
        $domain = config('freshdesk.domain');

        if (!$apiKey || !$domain) {
            $this->error('Missing required environment variables: FRESHDESK_API_KEY or FRESHDESK_DOMAIN');
            Log::error('Missing required environment variables');
            return 1;
        }

        $freshdeskClient = new FreshdeskClient(
            apiKey: (string) $apiKey,
            domain: (string) $domain,
        );

        try {
            $this->downloadTicketsList($freshdeskClient);
            $this->downloadTicketDetails($freshdeskClient);
            $this->info('Successfully completed tickets download');
            Log::info('Successfully completed tickets download');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Error during download: ' . $e->getMessage());
            Log::error('Error during download', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Загрузка списка задач постранично
     *
     * @return void
     * @throws \Exception
     */
    private function downloadTicketsList(FreshdeskClient $freshdeskClient): void
    {
        $this->info('Starting tickets list download...');
        Log::info('Starting tickets list download');

        $page = 1;
        $totalDownloaded = 0;

        while (true) {
            $this->info("Downloading page {$page}...");

            try {
                $response = $freshdeskClient->getTicketsList($page);

                if (empty($response)) {
                    $this->info('No more tickets found. Download completed.');
                    Log::info('No more tickets found. Download completed.', ['total_pages' => $page - 1]);
                    break;
                }

                $this->fileStorage->saveTicketsList($response, $page);
                $totalDownloaded += count($response);

                $this->info("Page {$page}: " . count($response) . ' tickets saved');

                $page++;

                sleep(1);
            } catch (\Throwable $e) {
                $this->error("Failed to download page {$page}: " . $e->getMessage());
                Log::error('Failed to download page', [
                    'page' => $page,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }

        $this->info("Total tickets downloaded: {$totalDownloaded}");
        Log::info('Total tickets downloaded', ['count' => $totalDownloaded]);
    }

    /**
     * Загрузка детальной информации по каждой задаче
     *
     * @return void
     * @throws \Exception
     */
    private function downloadTicketDetails(FreshdeskClient $freshdeskClient): void
    {
        $this->info('Starting ticket details download...');
        Log::info('Starting ticket details download');

        $listDirectory = '/var/www/backend/storage/tickets/list';

        if (!is_dir($listDirectory)) {
            $this->error("Directory not found: {$listDirectory}");
            Log::error('Directory not found', ['directory' => $listDirectory]);
            return;
        }

        $files = glob($listDirectory . '/page_*.json');

        if (empty($files)) {
            $this->warn('No ticket list files found');
            Log::warning('No ticket list files found');
            return;
        }

        $totalDetails = 0;

        foreach ($files as $file) {
            $this->info("Processing file: {$file}");

            $content = file_get_contents($file);
            if ($content === false) {
                $this->error("Failed to read file: {$file}");
                Log::error('Failed to read file', ['file' => $file]);
                continue;
            }

            $data = json_decode($content, true);
            if (!$data || !isset($data) || !is_array($data)) {
                $this->warn("Invalid format in file: {$file}");
                Log::warning('Invalid format in file', ['file' => $file]);
                continue;
            }

            foreach ($data as $ticket) {
                if (!isset($ticket['id'])) {
                    $this->warn('Ticket without ID found, skipping');
                    Log::warning('Ticket without ID found');
                    continue;
                }

                $ticketId = (int) $ticket['id'];

                try {
                    $this->info("Downloading details for ticket {$ticketId}...");
                    $details = $freshdeskClient->getTicketDetails($ticketId);
                    $this->fileStorage->saveTicketDetails($details, $ticketId);
                    $totalDetails++;

                    sleep(1);
                } catch (\Throwable $e) {
                    $this->error("Failed to download details for ticket {$ticketId}: " . $e->getMessage());
                    Log::error('Failed to download ticket details', [
                        'ticket_id' => $ticketId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("Total ticket details downloaded: {$totalDetails}");
        Log::info('Total ticket details downloaded', ['count' => $totalDetails]);
    }
}
